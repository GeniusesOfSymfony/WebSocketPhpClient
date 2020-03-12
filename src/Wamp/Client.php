<?php

namespace Gos\Component\WebSocketClient\Wamp;

use Gos\Component\WebSocketClient\Exception\BadResponseException;
use Gos\Component\WebSocketClient\Exception\WebsocketException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var bool
     */
    protected $closing = false;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string|null
     */
    protected $target;

    /**
     * @var resource|null
     */
    protected $socket;

    /**
     * @var string|null
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $serverHost;

    /**
     * @var int
     */
    protected $serverPort;

    /**
     * @var bool
     */
    protected $secured = false;

    /**
     * @var string|null
     */
    protected $origin;

    public function __construct(string $host, int $port, bool $secured = false, ?string $origin = null)
    {
        $this->serverHost = $host;
        $this->serverPort = $port;
        $this->secured = $secured;
        $this->origin = null !== $origin ? $origin : $host;

        $this->endpoint = sprintf(
            '%s://%s:%s',
            $secured ? 'ssl' : 'tcp',
            $host,
            $port
        );
    }

    /**
     * @return string The session identifier for the connection
     *
     * @throws BadResponseException if a response could not be received from the websocket server
     * @throws WebsocketException if the target URI is invalid
     */
    public function connect(string $target = '/'): string
    {
        if ($this->connected) {
            return $this->sessionId;
        }

        $socket = @stream_socket_client($this->endpoint, $errno, $errstr);

        if ($socket === false) {
            throw new BadResponseException('Could not open socket. Reason: '.$errstr);
        }

        $this->target = $target;
        $this->socket = $socket;

        $this->verifyResponse($this->upgradeProtocol($this->target));

        $payload = json_decode($this->read());

        if ($payload === false) {
            throw new BadResponseException('WAMP Server sent an invalid payload.');
        }

        if (Protocol::MSG_WELCOME !== $payload[0]) {
            throw new BadResponseException('WAMP Server did not send a welcome message.');
        }

        $this->connected = true;

        return $this->sessionId = $payload[1];
    }

    /**
     * @return string|false Response body from the request or boolean false on failure
     *
     * @throws WebsocketException if the target URI is invalid
     */
    protected function upgradeProtocol(string $target)
    {
        $key = $this->generateKey();

        if (false === strpos($target, '/')) {
            throw new WebsocketException('WAMP server target must contain a "/"');
        }

        $protocol = $this->secured ? 'wss' : 'ws';

        $out = "GET {$protocol}://{$this->serverHost}:{$this->serverPort}{$target} HTTP/1.1\r\n";
        $out .= "Host: {$this->serverHost}:{$this->serverPort}\r\n";
        $out .= "Pragma: no-cache\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Upgrade: WebSocket\r\n";
        $out .= "Connection: Upgrade\r\n";
        $out .= "Sec-WebSocket-Key: $key\r\n";
        $out .= "Sec-WebSocket-Protocol: wamp\r\n";
        $out .= "Sec-WebSocket-Version: 13\r\n";
        $out .= "Origin: {$this->origin}\r\n\r\n";

        fwrite($this->socket, $out);

        return fgets($this->socket);
    }

    /**
     * @param string|false $response Response body from the upgrade request or boolean false on failure
     *
     * @throws BadResponseException if an invalid response was received
     */
    protected function verifyResponse($response): void
    {
        if (false === $response) {
            throw new BadResponseException('WAMP Server did not respond properly');
        }

        $responseStatus = substr($response, 0, 12);

        if ('HTTP/1.1 101' !== $responseStatus) {
            throw new BadResponseException(sprintf('Unexpected response status. Expected "HTTP/1.1 101", got "%s".', $responseStatus));
        }
    }

    /**
     * Read the buffer and return the oldest event in stack.
     *
     * @see https://tools.ietf.org/html/rfc6455#section-5.2
     *
     * @throws BadResponseException if the buffer could not be read
     */
    protected function read(): string
    {
        $streamBody = stream_get_contents($this->socket, stream_get_meta_data($this->socket)['unread_bytes']);

        if ($streamBody === false) {
            throw new BadResponseException('The stream buffer could not be read.');
        }

        $startPos = strpos($streamBody, '[');
        $endPos = strpos($streamBody, ']');

        if ($startPos === false || $endPos === false) {
            throw new BadResponseException('Could not extract response body from stream.');
        }

        return substr(
            $streamBody,
            $startPos,
            $endPos
        );
    }

    /**
     * @throws WebsocketException if the connection could not be disconnected cleanly
     */
    public function disconnect(): bool
    {
        if (false === $this->connected) {
            return true;
        }

        if (null === $this->socket) {
            return true;
        }

        $this->send((new WebsocketPayload())->generateClosePayload(), WebsocketPayload::OPCODE_CLOSE);

        $firstByte = fread($this->socket, 1);

        if ($firstByte === false) {
            throw new WebsocketException('Could not extract the payload from the buffer.');
        }

        $payloadLength = \ord($firstByte);
        $payload = fread($this->socket, $payloadLength);

        if ($payload === false) {
            throw new WebsocketException('Could not extract the payload from the buffer.');
        }

        if ($payloadLength >= 2) {
            $bin = $payload[0].$payload[1];
            $status = bindec(sprintf('%08b%08b', \ord($payload[0]), \ord($payload[1])));

            $this->send($bin.'Close acknowledged: '.$status, WebsocketPayload::OPCODE_CLOSE);
        }

        fclose($this->socket);
        $this->connected = false;

        return true;
    }

    /**
     * @param array|string $data Any JSON encodable data
     *
     * @throws WebsocketException if the data cannot be encoded properly
     */
    protected function send($data, int $opcode = WebsocketPayload::OPCODE_TEXT, bool $masked = true): void
    {
        $mask = $masked ? 0x1 : 0x0;

        $payload = json_encode($data);

        if ($payload === false) {
            throw new WebsocketException('The data could not be encoded: '.json_last_error_msg());
        }

        $encoded = (new WebsocketPayload())
            ->setOpcode($opcode)
            ->setMask($mask)
            ->setPayload($payload)
            ->encodePayload();

        // Check if the connection was reset, if so try to reconnect
        if (0 === @fwrite($this->socket, $encoded)) {
            $this->connected = false;
            $this->connect($this->target);

            fwrite($this->socket, $encoded);
        }
    }

    /**
     * Establish a prefix on server.
     *
     * @see http://wamp.ws/spec#prefix_message
     */
    public function prefix(string $prefix, string $uri): void
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Establishing prefix "%s" for URI "%s"', $prefix, $uri));
        }

        $this->send([Protocol::MSG_PREFIX, $prefix, $uri]);
    }

    /**
     * Call a procedure on server.
     *
     * @see http://wamp.ws/spec#call_message
     *
     * @param array|mixed $args Arguments for the message either as an array or variadic set of parameters
     */
    public function call(string $procUri, $args): void
    {
        if (!is_array($args)) {
            $args = \func_get_args();
            array_shift($args);
        }

        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('Websocket client calling %s', $procUri),
                [
                    'callArguments' => $args,
                ]
            );
        }

        $this->send(
            array_merge(
                [Protocol::MSG_CALL, uniqid('', true), $procUri],
                $args
            )
        );
    }

    /**
     * The client will send an event to all clients connected to the server who have subscribed to the topicURI.
     *
     * @see http://wamp.ws/spec#publish_message
     *
     * @param string[] $exclude
     * @param string[] $eligible
     */
    public function publish(string $topicUri, string $payload, array $exclude = [], array $eligible = []): void
    {
        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('Websocket client publishing to %s', $topicUri),
                [
                    'payload' => $payload,
                    'excludedIds' => $exclude,
                    'eligibleIds' => $eligible,
                ]
            );
        }

        $this->send([Protocol::MSG_PUBLISH, $topicUri, $payload, $exclude, $eligible]);
    }

    /**
     * Subscribers receive PubSub events published by subscribers via the EVENT message. The EVENT message contains the topicURI, the topic under which the event was published, and event, the PubSub event payload.
     */
    public function event(string $topicUri, string $payload): void
    {
        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('Websocket client sending event to %s', $topicUri),
                [
                    'payload' => $payload,
                ]
            );
        }

        $this->send([Protocol::MSG_EVENT, $topicUri, $payload]);
    }

    protected function generateKey(int $length = 16): string
    {
        $c = 0;
        $tmp = '';

        while ($c++ * 16 < $length) {
            $tmp .= md5((string) mt_rand(), true);
        }

        return base64_encode(substr($tmp, 0, $length));
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }
}

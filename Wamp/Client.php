<?php

namespace Gos\Component\WebSocketClient\Wamp;

use Gos\Component\WebSocketClient\Exception\BadResponseException;
use Gos\Component\WebSocketClient\Exception\WebsocketException;
use Psr\Log\LoggerInterface;

/**
 * WS Client.
 *
 * @author Martin Bažík <martin@bazo.sk>
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Client
{
    /** @var string */
    protected $endpoint;

    /** @var string */
    protected $serverHost;

    /** @var int */
    protected $serverPort;

    /** @var resource */
    protected $socket;

    /** @var bool  */
    protected $connected;

    /** @var string */
    protected $sessionId;

    /** @var string */
    protected $origin;

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $closing;

    /** @var  bool */
    protected $secured;

    /** @var  string */
    protected $target;

    /**
     * @param string     $host
     * @param int|string $port
     * @param bool       $secured
     * @param string     $origin
     */
    public function __construct($host, $port, $secured = false, $origin = null)
    {
        $this->serverHost = $host;
        $this->connected = false;
        $this->closing = false;
        $this->secured = $secured;
        $this->serverPort = $port;

        if (null === $origin) {
            $origin = $host;
        }

        $this->origin = $origin;

        $protocol = (true === $this->secured ? 'ssl' : 'tcp');

        $this->endpoint = sprintf(
            '%s://%s:%s',
            $protocol,
            $host,
            $port
        );
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setAuthenticationToken()
    {
        /* @todo  **/
    }

    /**
     * @param string $target
     *
     * @return string
     *
     * @throws BadResponseException
     * @throws WebsocketException
     */
    public function connect($target = '/')
    {
        $this->target = $target;

        if ($this->connected) {
            return $this->sessionId;
        }

        $this->socket = @stream_socket_client($this->endpoint, $errno, $errstr);

        if (!$this->socket) {
            throw new BadResponseException('Could not open socket. Reason: ' . $errstr);
        }

        $response = $this->upgradeProtocol($this->target);

        $this->verifyResponse($response);

        $payload = json_decode($this->read());

        if ($payload[0] != Protocol::MSG_WELCOME) {
            throw new BadResponseException('WAMP Server did not send welcome message.');
        }

        $this->sessionId = $payload[1];

        $this->connected = true;

        return $this->sessionId;
    }

    /**
     * @param string $target
     *
     * @return string
     *
     * @throws WebsocketException
     */
    protected function upgradeProtocol($target)
    {
        $key = $this->generateKey();

        if (false === strpos($target, '/')) {
            throw new WebsocketException('Wamp Server Target is wrong.');
        }

        $protocol = true === $this->secured ? 'wss' : 'ws';

        $out = "GET {$protocol}://{$this->serverHost}:{$this->serverPort}{$target} HTTP/1.1\r\n";
        $out .= "Host: {$this->serverHost}:{$this->serverPort}\r\n";
        $out .= "Pragma: no-cache\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Upgrade: WebSocket\r\n";
        $out .= "Connection: Upgrade\r\n";
        $out .= "Sec-WebSocket-Key: $key\r\n";
        $out .= "Sec-WebSocket-Protocol: wamp\r\n";

        //@todo support auth
//        $out .= "Cookie: PHPSESSID=2okar2mng0mklk62iutc0bert0\r\n";

        $out .= "Sec-WebSocket-Version: 13\r\n";
        $out .= "Origin: {$this->origin}\r\n\r\n";

        fwrite($this->socket, $out);

        return fgets($this->socket);
    }

    /**
     * @param $response
     *
     * @throws BadResponseException
     */
    protected function verifyResponse($response)
    {
        if (false === $response) {
            throw new BadResponseException('WAMP Server did not respond properly');
        }

        $subres = substr($response, 0, 12);

        if ($subres != 'HTTP/1.1 101') {
            throw new BadResponseException('Unexpected Response. Expected HTTP/1.1 101 got ' . $subres);
        }
    }

    /**
     * Read the buffer and return the oldest event in stack.
     *
     * @see https://tools.ietf.org/html/rfc6455#section-5.2
     *
     * @return string
     */
    protected function read()
    {
        // Ignore first byte
        fread($this->socket, 1);

        // There is also masking bit, as MSB, bit it's 0
        $payloadLength = ord(fread($this->socket, 1));

        switch ($payloadLength) {
            case 126:
                $payloadLength = unpack('n', fread($this->socket, 2));
                $payloadLength = $payloadLength[1];
                break;
            case 127:
                //$this->stdout('error', "Next 8 bytes are 64bit uint payload length, not yet implemented, since PHP can't handle 64bit longs!");
                break;
        }

        return fread($this->socket, $payloadLength);
    }

    /**
     * Disconnect.
     *
     * @return bool
     */
    public function disconnect()
    {
        if (false === $this->connected) {
            return true;
        }

        if ($this->socket) {
            $this->send(WebsocketPayload::generateClosePayload(), WebsocketPayload::OPCODE_CLOSE);
            $this->closing = true;

            $payloadLength = ord(fread($this->socket, 1));
            $payload = fread($this->socket, $payloadLength);

            if ($this->closing) {
                $this->closing = false;
            } else {
                if ($payloadLength >= 2) {
                    $bin = $payload[0] . $payload[1];
                    $status = bindec(sprintf('%08b%08b', ord($payload[0]), ord($payload[1])));

                    $this->send($bin . 'Close acknowledged: ' . $status, WebsocketPayload::OPCODE_CLOSE);
                }
            }

            fclose($this->socket);
            $this->connected = false;

            return true;
        }

        return false;
    }

    /**
     * Send message to the websocket.
     *
     *
     * @param array $data
     *
     * @return $this|Client
     */
    protected function send($data, $opcode = WebsocketPayload::OPCODE_TEXT, $masked = true)
    {
        $rawMessage = json_encode($data);
        $payload = new WebsocketPayload();
        $payload
            ->setOpcode($opcode)
            ->setMask($masked)
            ->setPayload($rawMessage);

        $encoded = $payload->encodePayload();

        if (0 === @fwrite($this->socket, $encoded)) { //connection reseted by peers, just reconnect.
            $this->connected = false;
            $this->connect($this->target);

            fwrite($this->socket, $encoded);
        }

        return $this;
    }

    /**
     * Establish a prefix on server.
     *
     * @see http://wamp.ws/spec#prefix_message
     *
     * @param string $prefix
     * @param string $uri
     */
    public function prefix($prefix, $uri)
    {
        $type = Protocol::MSG_PREFIX;
        $data = [$type, $prefix, $uri];
        $this->send($data);
    }

    /**
     * Call a procedure on server.
     *
     * @see http://wamp.ws/spec#call_message
     *
     * @param string $procURI
     * @param mixed  $arguments
     */
    public function call($procUri, $arguments = [])
    {
        $args = func_get_args();
        array_shift($args);
        $type = Protocol::MSG_CALL;
        $callId = uniqid('', $moreEntropy = true);
        $data = array_merge(array($type, $callId, $procUri), $args);

        $this->send($data);
    }

    /**
     * The client will send an event to all clients connected to the server who have subscribed to the topicURI.
     *
     * @see http://wamp.ws/spec#publish_message
     *
     * @param string $topicUri
     * @param string $payload
     * @param string $exclude
     * @param string $eligible
     */
    public function publish($topicUri, $payload, $exclude = [], $eligible = [])
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf(
                'Publish in %s',
                $topicUri
            ));
        }

        $data = array(Protocol::MSG_PUBLISH, $topicUri, $payload, $exclude, $eligible);
        $this->send($data);
    }

    /**
     * Subscribers receive PubSub events published by subscribers via the EVENT message. The EVENT message contains the topicURI, the topic under which the event was published, and event, the PubSub event payload.
     *
     * @param string $topicUri
     * @param string $payload
     */
    public function event($topicUri, $payload)
    {
        $type = Protocol::MSG_EVENT;
        $data = array($type, $topicUri, $payload);
        $this->send($data);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    protected function generateKey($length = 16)
    {
        $c = 0;
        $tmp = '';

        while ($c++ * 16 < $length) {
            $tmp .= md5(mt_rand(), true);
        }

        return base64_encode(substr($tmp, 0, $length));
    }
}

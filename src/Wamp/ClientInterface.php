<?php

namespace Gos\Component\WebSocketClient\Wamp;

use Gos\Component\WebSocketClient\Exception\BadResponseException;
use Gos\Component\WebSocketClient\Exception\WebsocketException;

interface ClientInterface
{
    /**
     * @return string The session identifier for the connection
     *
     * @throws BadResponseException if a response could not be received from the websocket server
     * @throws WebsocketException   if the target URI is invalid
     */
    public function connect(string $target = '/'): string;

    /**
     * @throws WebsocketException if the connection could not be disconnected cleanly
     */
    public function disconnect(): bool;

    public function isConnected(): bool;

    /**
     * Establish a prefix on server.
     *
     * @see http://wamp.ws/spec#prefix_message
     */
    public function prefix(string $prefix, string $uri): void;

    /**
     * Call a procedure on server.
     *
     * @see http://wamp.ws/spec#call_message
     *
     * @param array|mixed $args Arguments for the message either as an array or variadic set of parameters
     */
    public function call(string $procUri, $args): void;

    /**
     * The client will send an event to all clients connected to the server who have subscribed to the topicURI.
     *
     * @see http://wamp.ws/spec#publish_message
     *
     * @param string[] $exclude
     * @param string[] $eligible
     */
    public function publish(string $topicUri, string $payload, array $exclude = [], array $eligible = []): void;

    /**
     * Subscribers receive PubSub events published by subscribers via the EVENT message. The EVENT message contains the topicURI, the topic under which the event was published, and event, the PubSub event payload.
     */
    public function event(string $topicUri, string $payload): void;
}

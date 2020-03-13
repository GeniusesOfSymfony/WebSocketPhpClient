WebSocketPhpClient
==================

[![Latest Stable Version](https://poser.pugx.org/gos/websocket-client/v/stable)](https://packagist.org/packages/gos/websocket-client) [![Latest Unstable Version](https://poser.pugx.org/gos/websocket-client/v/unstable)](https://packagist.org/packages/gos/websocket-client) [![Total Downloads](https://poser.pugx.org/gos/websocket-client/downloads)](https://packagist.org/packages/gos/websocket-client) [![License](https://poser.pugx.org/gos/websocket-client/license)](https://packagist.org/packages/gos/websocket-client) [![Build Status](https://travis-ci.org/GeniusesOfSymfony/WebSocketPhpClient.svg)](https://travis-ci.org/GeniusesOfSymfony/WebSocketPhpClient)

## About

This package provides a PHP client that can send messages to a websocket server utilizing the WAMPv1 protocol. Listening for replies is not supported at this time.

Supported functions:
 - prefix
 - call
 - publish
 - event

## Usage

```php
use Gos\Component\WebSocketClient\Wamp\Client;

$client = new Client('127.0.0.1', '8080');
$sessionId = $client->connect();

// Establish a prefix on server
$client->prefix("calc", "http://example.com/simple/calc#");

// You can send an arbitrary number of arguments
$client->call('calc', 12, 14, 15);

$data = [0, 1, 2];

// Or an array
$client->call('calc', $data);

$exclude = [$sessionId]; // No sense in sending the payload to ourselves
$eligible = []; // List of other clients ids that are eligible to receive this payload

// $payload can be scalar or array
$client->publish('topic', [], $exclude, $eligible);

// Publish an event
$client->event('topic', []);
$client->disconnect();
```

## License
This software is distributed under MIT License. See LICENSE for more info.

## Original Project
[https://github.com/bazo/wamp-client](https://github.com/bazo/wamp-client)

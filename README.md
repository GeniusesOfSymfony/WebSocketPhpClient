WebSocketPhpClient
==================

[![Latest Stable Version](https://poser.pugx.org/gos/websocket-client/v/stable)](https://packagist.org/packages/gos/websocket-client) [![Latest Unstable Version](https://poser.pugx.org/gos/websocket-client/v/unstable)](https://packagist.org/packages/gos/websocket-client) [![Total Downloads](https://poser.pugx.org/gos/websocket-client/downloads)](https://packagist.org/packages/gos/websocket-client) [![License](https://poser.pugx.org/gos/websocket-client/license)](https://packagist.org/packages/gos/websocket-client)

## About

This package provides a PHP client that can send messages to a websocket server utilizing the WAMPv1 protocol. Listening for replies is not supported at this time.

Supported functions:
 - prefix
 - call
 - publish
 - event

## Usage

### Directly Create A Client

You can directly create a `Gos\Component\WebSocketClient\Wamp\ClientInterface` instance by creating a new `Gos\Component\WebSocketClient\Wamp\Client` object. The constructor has two mandatory requirements; the server host and port. You may review the [`Client class constructor`](/src/Wamp/Client.php) to see all arguments.

```php
<?php
use Gos\Component\WebSocketClient\Wamp\Client;

$client = new Client('127.0.0.1', 8080);
```

### Through The Factory

A `Gos\Component\WebSocketClient\Wamp\ClientFactoryInterface` is available to create client instances as well. The default `Gos\Component\WebSocketClient\Wamp\ClientFactory` supports a PSR-3 logger and will automatically inject it into the client if one is present.

```php
<?php
use Gos\Component\WebSocketClient\Wamp\ClientFactory;

$factory = new ClientFactory(['host' => '127.0.0.1', 'port' => 8080]);
$client = $factory->createConnection();
```

### Interact With Server

Once you have created a client, you can connect and interact with your websocket server.

```php
<?php
use Gos\Component\WebSocketClient\Wamp\ClientFactory;

$factory = new ClientFactory(['host' => '127.0.0.1', 'port' => 8080]);
$client = $factory->createConnection();

$sessionId = $client->connect();

// Establish a prefix on server
$client->prefix('calc', 'http://example.com/simple/calc#');

// You can send an arbitrary number of arguments
$client->call('calc', 12, 14, 15);

$data = [0, 1, 2];

// Or an array
$client->call('calc', $data);

$exclude = [$sessionId]; // No sense in sending the payload to ourselves
$eligible = []; // List of other clients ids that are eligible to receive this payload

$client->publish('topic', '', $exclude, $eligible);

// Publish an event
$client->event('topic', '');
$client->disconnect();
```

## License
This software is distributed under MIT License. See LICENSE for more info.

## Original Project
[https://github.com/bazo/wamp-client](https://github.com/bazo/wamp-client)

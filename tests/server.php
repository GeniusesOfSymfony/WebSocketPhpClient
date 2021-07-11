<?php

require __DIR__.'/../vendor/autoload.php';

use Ratchet\App;
use Ratchet\ConnectionInterface;
use Ratchet\Server\EchoServer;
use Ratchet\Wamp\WampServerInterface;

$app = new App('localhost', 19999);
$app->route(
    '/',
    new class() implements WampServerInterface {
        public function onOpen(ConnectionInterface $conn): void
        {
        }

        public function onClose(ConnectionInterface $conn): void
        {
        }

        public function onError(ConnectionInterface $conn, Exception $e): void
        {
        }

        public function onCall(ConnectionInterface $conn, $id, $topic, array $params): void
        {
        }

        public function onSubscribe(ConnectionInterface $conn, $topic): void
        {
        }

        public function onUnSubscribe(ConnectionInterface $conn, $topic): void
        {
        }

        public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible): void
        {
        }
    }
);
$app->route(
    '/echo',
    new EchoServer()
);
$app->run();

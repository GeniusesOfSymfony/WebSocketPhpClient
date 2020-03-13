<?php

require __DIR__.'/../vendor/autoload.php';

use Ratchet\App;
use Ratchet\ConnectionInterface;
use Ratchet\Server\EchoServer;
use Ratchet\Wamp\WampServerInterface;

$app = new App('localhost', 8000);
$app->route(
    '/',
    new class() implements WampServerInterface {
        public function onOpen(ConnectionInterface $conn)
        {
        }

        public function onClose(ConnectionInterface $conn)
        {
        }

        public function onError(ConnectionInterface $conn, \Exception $e)
        {
        }

        public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
        {
        }

        public function onSubscribe(ConnectionInterface $conn, $topic)
        {
        }

        public function onUnSubscribe(ConnectionInterface $conn, $topic)
        {
        }

        public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
        {
        }
    }
);
$app->route(
    '/echo',
    new EchoServer()
);
$app->run();

<?php

namespace Gos\Component\WebSocketClient\Wamp;

interface WampConnectionFactoryInterface
{
    public function createConnection(): ClientInterface;
}

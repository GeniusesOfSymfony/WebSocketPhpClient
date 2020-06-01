<?php

namespace Gos\Component\WebSocketClient\Wamp;

interface ClientFactoryInterface
{
    public function createConnection(): ClientInterface;
}

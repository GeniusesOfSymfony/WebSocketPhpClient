<?php

namespace Gos\Component\WebSocketClient\Wamp;

interface PayloadGeneratorInterface
{
    public function encode(WebsocketPayload $websocketPayload): string;

    public function generateClosePayload(): string;
}

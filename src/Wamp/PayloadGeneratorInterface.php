<?php

namespace Gos\Component\WebSocketClient\Wamp;

trigger_deprecation('gos/websocket-client', '1.2', 'The package is deprecated, use "ratchet/pawl" instead.');

/**
 * @deprecated the package is deprecated, use "ratchet/pawl" instead.
 */
interface PayloadGeneratorInterface
{
    public function encode(WebsocketPayload $websocketPayload): string;

    public function generateClosePayload(): string;
}

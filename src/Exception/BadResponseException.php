<?php

namespace Gos\Component\WebSocketClient\Exception;

trigger_deprecation('gos/websocket-client', '1.2', 'The package is deprecated, use "ratchet/pawl" instead.');

/**
 * @deprecated the package is deprecated, use "ratchet/pawl" instead.
 */
class BadResponseException extends WebsocketException
{
}

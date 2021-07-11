<?php

namespace Gos\Component\WebSocketClient\Wamp;

trigger_deprecation('gos/websocket-client', '1.2', 'The package is deprecated, use "ratchet/pawl" instead.');

/**
 * @deprecated the package is deprecated, use "ratchet/pawl" instead
 */
final class Protocol
{
    public const MSG_WELCOME = 0;
    public const MSG_PREFIX = 1;
    public const MSG_CALL = 2;
    public const MSG_CALL_RESULT = 3;
    public const MSG_CALL_ERROR = 4;
    public const MSG_SUBSCRIBE = 5;
    public const MSG_UNSUBSCRIBE = 6;
    public const MSG_PUBLISH = 7;
    public const MSG_EVENT = 8;

    private function __construct()
    {
    }
}

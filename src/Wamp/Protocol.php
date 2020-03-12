<?php

namespace Gos\Component\WebSocketClient\Wamp;

/**
 * Description of Protocol.
 *
 * @author Martin Bažík <martin@bazo.sk>
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
abstract class Protocol
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
}

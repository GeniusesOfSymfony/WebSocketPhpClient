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
    const MSG_WELCOME = 0;
    const MSG_PREFIX = 1;
    const MSG_CALL = 2;
    const MSG_CALL_RESULT = 3;
    const MSG_CALL_ERROR = 4;
    const MSG_SUBSCRIBE = 5;
    const MSG_UNSUBSCRIBE = 6;
    const MSG_PUBLISH = 7;
    const MSG_EVENT = 8;
}

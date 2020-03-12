<?php

namespace Gos\Component\WebSocketClient\Tests\Wamp;

use Gos\Component\WebSocketClient\Wamp\PayloadGenerator;
use PHPUnit\Framework\TestCase;

final class PayloadGeneratorTest extends TestCase
{
    public function testAClosePayloadIsGenerated(): void
    {
        $this->assertStringEndsWith('ttfn', (new PayloadGenerator())->generateClosePayload());
    }
}

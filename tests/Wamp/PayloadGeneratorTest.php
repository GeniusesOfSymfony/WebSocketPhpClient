<?php

namespace Gos\Component\WebSocketClient\Tests\Wamp;

use Gos\Component\WebSocketClient\Wamp\PayloadGenerator;
use Gos\Component\WebSocketClient\Wamp\Protocol;
use Gos\Component\WebSocketClient\Wamp\WebsocketPayload;
use PHPUnit\Framework\TestCase;

final class PayloadGeneratorTest extends TestCase
{
    public function testAnUnmaskedPayloadIsEncodedForAPrefixMessageAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x0)
            ->setPayload(json_encode([Protocol::MSG_PREFIX, 'test', 'http://localhost/test']));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAMaskedPayloadIsEncodedForAPrefixMessageAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x1)
            ->setPayload(json_encode([Protocol::MSG_PREFIX, 'test', 'http://localhost/test']));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAnUnmaskedPayloadIsEncodedForACallMessageWithAMediumPayloadAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x0)
            ->setPayload(json_encode([Protocol::MSG_CALL, uniqid('', true), '/test', str_repeat('a', 0xc8)]));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAMaskedPayloadIsEncodedForACallMessageWithAMediumPayloadAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x1)
            ->setPayload(json_encode([Protocol::MSG_CALL, uniqid('', true), '/test', str_repeat('a', 0xc8)]));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAnUnmaskedPayloadIsEncodedForACallMessageWithALongPayloadAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x0)
            ->setPayload(json_encode([Protocol::MSG_CALL, uniqid('', true), '/test', str_repeat('a', 0x10000)]));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAMaskedPayloadIsEncodedForACallMessageWithALongPayloadAsATextOpcode(): void
    {
        $payload = (new WebsocketPayload())
            ->setOpcode(WebsocketPayload::OPCODE_TEXT)
            ->setMask(0x1)
            ->setPayload(json_encode([Protocol::MSG_CALL, uniqid('', true), '/test', str_repeat('a', 0x10000)]));

        $this->assertNotEmpty((new PayloadGenerator())->encode($payload));
    }

    public function testAClosePayloadIsGenerated(): void
    {
        $this->assertStringEndsWith('ttfn', (new PayloadGenerator())->generateClosePayload());
    }
}

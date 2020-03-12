<?php

namespace Gos\Component\WebSocketClient\Wamp;

final class WebsocketPayload
{
    public const OPCODE_CONTINUE = 0x0;
    public const OPCODE_TEXT = 0x1;
    public const OPCODE_BINARY = 0x2;
    public const OPCODE_NON_CONTROL_RESERVED_1 = 0x3;
    public const OPCODE_NON_CONTROL_RESERVED_2 = 0x4;
    public const OPCODE_NON_CONTROL_RESERVED_3 = 0x5;
    public const OPCODE_NON_CONTROL_RESERVED_4 = 0x6;
    public const OPCODE_NON_CONTROL_RESERVED_5 = 0x7;
    public const OPCODE_CLOSE = 0x8;
    public const OPCODE_PING = 0x9;
    public const OPCODE_PONG = 0xA;
    public const OPCODE_CONTROL_RESERVED_1 = 0xB;
    public const OPCODE_CONTROL_RESERVED_2 = 0xC;
    public const OPCODE_CONTROL_RESERVED_3 = 0xD;
    public const OPCODE_CONTROL_RESERVED_4 = 0xE;
    public const OPCODE_CONTROL_RESERVED_5 = 0xF;

    /**
     * @var int
     */
    private $fin = 0x1;

    /**
     * @var int
     */
    private $rsv1 = 0x0;

    /**
     * @var int
     */
    private $rsv2 = 0x0;

    /**
     * @var int
     */
    private $rsv3 = 0x0;

    /**
     * @var int|null
     */
    private $opcode;

    /**
     * @var int
     */
    private $mask = 0x0;

    /**
     * @var string
     */
    private $maskKey;

    /**
     * @var string|null
     */
    private $payload;

    public function setFin(int $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getFin(): int
    {
        return $this->fin;
    }

    public function setRsv1(int $rsv1): self
    {
        $this->rsv1 = $rsv1;

        return $this;
    }

    public function getRsv1(): int
    {
        return $this->rsv1;
    }

    public function setRsv2(int $rsv2): self
    {
        $this->rsv2 = $rsv2;

        return $this;
    }

    public function getRsv2(): int
    {
        return $this->rsv2;
    }

    public function setRsv3(int $rsv3): self
    {
        $this->rsv3 = $rsv3;

        return $this;
    }

    public function getRsv3(): int
    {
        return $this->rsv3;
    }

    public function setOpcode(?int $opcode): self
    {
        $this->opcode = $opcode;

        return $this;
    }

    public function getOpcode(): ?int
    {
        return $this->opcode;
    }

    public function setMask(int $mask): self
    {
        $this->mask = $mask;

        if (true == $this->mask) {
            $this->generateMaskKey();
        }

        return $this;
    }

    public function getMask(): int
    {
        return $this->mask;
    }

    public function getLength(): int
    {
        return \strlen($this->getPayload());
    }

    public function setMaskKey(string $maskKey): self
    {
        $this->maskKey = $maskKey;

        return $this;
    }

    public function getMaskKey(): ?string
    {
        return $this->maskKey;
    }

    public function setPayload(?string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function generateMaskKey(): string
    {
        $this->setMaskKey(random_bytes(4));

        return $this->getMaskKey();
    }
}

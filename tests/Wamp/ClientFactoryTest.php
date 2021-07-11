<?php

namespace Gos\Component\WebSocketClient\Tests\Wamp;

use Gos\Component\WebSocketClient\Wamp\ClientFactory;
use Gos\Component\WebSocketClient\Wamp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

final class ClientFactoryTest extends TestCase
{
    public function dataInvalidConfiguration(): \Generator
    {
        yield 'host as a number' => [
            [
                'host' => 42,
                'port' => 1337,
            ],
            InvalidOptionsException::class,
        ];

        yield 'host missing' => [
            [
                'port' => 1337,
            ],
            MissingOptionsException::class,
            'The required option "host" is missing.',
        ];
    }

    public function dataValidConfiguration(): \Generator
    {
        yield 'filling in missing required parameters' => [
            [
                'host' => 'localhost',
                'port' => 1337,
            ],
        ];

        yield 'configuring all parameters' => [
            [
                'host' => 'localhost',
                'port' => 1337,
                'ssl' => true,
                'origin' => 'localhost',
            ],
        ];
    }

    /**
     * @dataProvider dataValidConfiguration
     */
    public function testTheFactoryIsCreatedWithAValidConfiguration(array $config): void
    {
        $this->assertInstanceOf(ClientFactory::class, new ClientFactory($config));
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     *
     * @dataProvider dataInvalidConfiguration
     */
    public function testTheFactoryIsNotCreatedWithAnInvalidConfiguration(
        array $config,
        string $exceptionClass,
        ?string $exceptionMessage = null
    ): void {
        $this->expectException($exceptionClass);

        if (null !== $exceptionMessage) {
            $this->expectExceptionMessage($exceptionMessage);
        }

        $this->assertInstanceOf(ClientFactory::class, new ClientFactory($config));
    }

    public function testTheConnectionObjectIsCreated(): void
    {
        $config = [
            'host' => 'localhost',
            'port' => 1337,
        ];

        $connection = (new ClientFactory($config))->createConnection();

        $this->assertInstanceOf(ClientInterface::class, $connection);
    }
}

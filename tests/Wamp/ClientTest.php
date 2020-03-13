<?php

namespace Gos\Component\WebSocketClient\Tests\Wamp;

use Gos\Component\WebSocketClient\Exception\WebsocketException;
use Gos\Component\WebSocketClient\Wamp\Client;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    /**
     * @var resource
     */
    private $server;

    protected function tearDown(): void
    {
        if (null !== $this->server) {
            proc_terminate($this->server, SIGKILL);

            sleep(1);
        }

        parent::tearDown();
    }

    public function testCanOpenAndCloseAConnectionToAWebsocketServer(): void
    {
        $this->startServer();

        $client = new Client('localhost', 8000, false);
        $this->assertNotEmpty($client->connect(), 'Opening a connection should return the session identifier');
        $this->assertTrue($client->disconnect());
    }

    public function testCannotOpenAConnectionWithAnInvalidTarget(): void
    {
        $this->expectException(WebsocketException::class);
        $this->expectExceptionMessage('WAMP server target must contain a "/"');

        $this->startServer();

        (new Client('localhost', 8000, false))->connect('');
    }

    public function testCannotOpenAConnectionWhenSocketConnectionCannotBeMade(): void
    {
        $this->expectException(WebsocketException::class);
        $this->expectExceptionMessage('Could not open socket. Reason: Connection refused');

        (new Client('localhost', 8000, false))->connect();
    }

    public function testCanEstablishAPrefixOnTheServer(): void
    {
        $this->startServer();

        $client = new Client('localhost', 8000, false);
        $this->assertNotEmpty($client->connect(), 'Opening a connection should return the session identifier');

        $client->prefix('/echo', 'http://example.com/echo');

        $this->assertTrue($client->disconnect());
    }

    public function testCanCallAProcedureOnTheServer(): void
    {
        $this->startServer();

        $client = new Client('localhost', 8000, false);
        $this->assertNotEmpty($client->connect(), 'Opening a connection should return the session identifier');

        $client->call('/echo', 'testing');

        $this->assertTrue($client->disconnect());
    }

    public function testCanPublishAMessageToTheServer(): void
    {
        $this->startServer();

        $client = new Client('localhost', 8000, false);
        $this->assertNotEmpty($client->connect(), 'Opening a connection should return the session identifier');

        $client->publish('/echo', json_encode(['message' => 'Testing']));

        $this->assertTrue($client->disconnect());
    }

    public function testCanSendAnEventToTheServer(): void
    {
        $this->startServer();

        $client = new Client('localhost', 8000, false);
        $this->assertNotEmpty($client->connect(), 'Opening a connection should return the session identifier');

        $client->event('/echo', json_encode(['message' => 'Testing']));

        $this->assertTrue($client->disconnect());
    }

    private function startServer(): void
    {
        $filename = realpath(\dirname(__DIR__).'/server.php');

        $pipes = [];
        $server = proc_open('php '.$filename, [], $pipes);

        if (false === $server) {
            $this->fail('Could not start server');
        }

        // Need to wait for the server to be ready
        sleep(1);

        $this->server = $server;
    }
}

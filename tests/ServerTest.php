<?php declare(strict_types=1);

namespace Mammatus\Tests\Metrics\Prometheus;

use Clue\React\Buzz\Browser;
use Mammatus\LifeCycleEvents\Initialize;
use Mammatus\LifeCycleEvents\Shutdown;
use Mammatus\Metrics\Prometheus\Server;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server as HttpServer;
use React\Promise\PromiseInterface;
use Throwable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use function assert;
use function React\Promise\resolve;

final class ServerTest extends AsyncTestCase
{
    /**
     * @test
     */
    final public function happyFlow(): void
    {
        $loop       = Factory::create();
        $httpServer = new HttpServer(function (): ResponseInterface {
            ($this->expectCallableOnce())();

            return new Response(200, [], 'yay');
        });
        $browser    = new Browser($loop);

        $server = new Server($loop, $httpServer);

        $response = $this->await($browser->get('http://localhost:7331/')->then(null, static function (Throwable $throwable): PromiseInterface {
            return resolve($throwable);
        }), $loop);
        assert($response instanceof Throwable);
        self::assertSame('All attempts to connect to "localhost" have failed', $response->getMessage());

        $server->stop(new Shutdown());

        $response = $this->await($browser->get('http://localhost:7331/')->then(null, static function (Throwable $throwable): PromiseInterface {
            return resolve($throwable);
        }), $loop);
        assert($response instanceof Throwable);
        self::assertSame('All attempts to connect to "localhost" have failed', $response->getMessage());

        $server->start(new Initialize());

        $response = $this->await($browser->get('http://localhost:7331/'), $loop);
        assert($response instanceof ResponseInterface);
        self::assertSame('yay', (string) $response->getBody());

        $server->stop(new Shutdown());

        $response = $this->await($browser->get('http://localhost:7331/')->then(null, static function (Throwable $throwable): PromiseInterface {
            return resolve($throwable);
        }), $loop);
        assert($response instanceof Throwable);
        self::assertSame('All attempts to connect to "localhost" have failed', $response->getMessage());
    }
}

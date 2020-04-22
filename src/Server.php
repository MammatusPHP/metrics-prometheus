<?php declare(strict_types=1);

namespace Mammatus\Metrics\Prometheus;

use Mammatus\LifeCycleEvents\Initialize;
use Mammatus\LifeCycleEvents\Shutdown;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use WyriHaximus\Broadcast\Contracts\Listener;

final class Server implements Listener
{
    private LoopInterface $loop;
    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?SocketServer $socket = null;
    private HttpServer $http;

    public function __construct(LoopInterface $loop, HttpServer $http)
    {
        $this->loop = $loop;
        $this->http = $http;
    }

    public function start(Initialize $event): void
    {
        $this->socket = new SocketServer('0.0.0.0:7331', $this->loop);
        $this->http->listen($this->socket);
    }

    public function stop(Shutdown $event): void
    {
        if (! ($this->socket instanceof SocketServer)) {
            return;
        }

        $this->socket->close();
    }
}

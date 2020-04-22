<?php declare(strict_types=1);

use Mammatus\Metrics\Prometheus\Server;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use ReactInspector\Http\Middleware\Printer\PrinterMiddleware;
use ReactInspector\HttpMiddleware\MiddlewareCollector;
use ReactInspector\MetricsStreamInterface;
use ReactInspector\Printer\Prometheus\PrometheusPrinter;
use function DI\factory;
use function DI\get;

return [
    'mammatus.metrics.prometheus.middleware.metrics' => new MiddlewareCollector('metrics'),
    Server::class => factory(function (
        LoopInterface $loop,
        MetricsStreamInterface $stream,
        MiddlewareCollector $measure
    ): Server {
        return new Server(
            $loop,
            new HttpServer([
                $measure,
                new PrinterMiddleware(new PrometheusPrinter(), $stream),
            ])
        );

    })->parameter('measure', get('config.mammatus.metrics.prometheus.middleware.metrics')),
];

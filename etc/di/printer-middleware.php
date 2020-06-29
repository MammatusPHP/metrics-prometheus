<?php declare(strict_types=1);

use ReactInspector\Http\Middleware\Printer\PrinterMiddleware;
use ReactInspector\MetricsStreamInterface;
use ReactInspector\Printer\Prometheus\PrometheusPrinter;
use function DI\factory;
use function DI\get;

return [
    PrinterMiddleware::class => factory(function (
        MetricsStreamInterface $stream
    ): PrinterMiddleware {
        return new PrinterMiddleware(new PrometheusPrinter(), $stream);
    }),
];

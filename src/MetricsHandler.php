<?php declare(strict_types=1);

namespace Mammatus\Metrics\Prometheus;

use Chimera\Mapping\Routing\FetchEndpoint;
use Mammatus\Http\Server\Annotations\Bus;
use Mammatus\Http\Server\Annotations\Vhost;
use React\Promise\PromiseInterface;
use ReactInspector\Http\Middleware\Printer\PrinterMiddleware;
use RingCentral\Psr7\ServerRequest;

/**
 * @Vhost("metrics")
 * @FetchEndpoint(app="metrics", path="/metrics", query=FetchMetrics::class, name="FetchMetrics")
 */
final class MetricsHandler
{
    private PrinterMiddleware $printerMiddleware;

    public function __construct(PrinterMiddleware $printerMiddleware)
    {
        $this->printerMiddleware = $printerMiddleware;
    }

    public function handle(FetchMetrics $query): PromiseInterface
    {
        return ($this->printerMiddleware)(new ServerRequest('GET', '/metrics'));
    }
}

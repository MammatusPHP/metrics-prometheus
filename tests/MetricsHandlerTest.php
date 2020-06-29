<?php declare(strict_types=1);

namespace Mammatus\Tests\Metrics\Prometheus;

use Chimera\Input;
use Mammatus\Metrics\Prometheus\FetchMetrics;
use Mammatus\Metrics\Prometheus\MetricsHandler;
use Psr\Http\Message\ResponseInterface;
use ReactInspector\Config;
use ReactInspector\Http\Middleware\Printer\PrinterMiddleware;
use ReactInspector\Measurement;
use ReactInspector\Measurements;
use ReactInspector\Metric;
use ReactInspector\MetricsStreamInterface;
use ReactInspector\Printer\Prometheus\PrometheusPrinter;
use ReactInspector\Tag;
use ReactInspector\Tags;
use Rx\Subject\Subject;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use function assert;

final class MetricsHandlerTest extends AsyncTestCase
{
    /**
     * @test
     */
    final public function forwardToMiddleware(): void
    {
        $input          = new class implements Input {
            /**
             * @param mixed|null $default
             *
             * @return mixed|null
             */
            public function getAttribute(string $name, $default = null)
            {
                return $default;
            }

            /**
             * @return mixed[]
             */
            public function getData(): array
            {
                return [];
            }
        };
        $metricsStream  = new class() extends Subject implements MetricsStreamInterface {
        };
        $metricsHandler = new MetricsHandler(new PrinterMiddleware(new PrometheusPrinter(), $metricsStream));
        $metric         = new Metric(
            new Config(
                'name',
                'counter',
                'description',
            ),
            new Tags(
                new Tag('key', 'value')
            ),
            new Measurements(
                new Measurement(1.23, new Tags())
            ),
            234234423.234
        );
        $metricsStream->onNext($metric);

        $response = $this->await($metricsHandler->handle(FetchMetrics::fromInput($input)));
        assert($response instanceof ResponseInterface);
        $responseBody = (string) $response->getBody();

        self::assertStringContainsString('# HELP name description', $responseBody);
        self::assertStringContainsString('# TYPE name counter', $responseBody);
        self::assertStringContainsString('name{key="value"} 1.23 234234423234', $responseBody);
    }
}

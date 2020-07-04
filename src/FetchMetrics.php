<?php declare(strict_types=1);

namespace Mammatus\Metrics\Prometheus;

use Chimera\Input;

final class FetchMetrics
{
    public static function fromInput(Input $input): FetchMetrics
    {
        return new self();
    }
}

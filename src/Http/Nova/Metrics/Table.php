<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Illuminate\Support\Carbon;
use Laravel\Nova\Metrics\Table as TableMetric;

class Table extends TableMetric
{
    public $width = '1/4';

    public function cacheFor(): Carbon
    {
        return now()->addMinute();
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Illuminate\Support\Carbon;
use Laravel\Nova\Metrics\Trend as TrendMetric;

class Trend extends TrendMetric
{
    public $width = '1/4';

    public function ranges(): array
    {
        return [
            7 => __('1 Week'),
            14 => __('2 Weeks'),
            21 => __('3 Weeks'),
            30 => __('1 Month'),
            60 => __('2 Months'),
            90 => __('3 Months'),
            180 => __('6 Months'),
            365 => __('1 Year'),
            730 => __('2 Years'),
        ];
    }

    public function cacheFor(): Carbon
    {
        return now()->addMinute();
    }
}

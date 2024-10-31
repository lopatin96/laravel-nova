<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Illuminate\Support\Carbon;
use Laravel\Nova\Metrics\Value as ValueMetric;

class Value extends ValueMetric
{
    public $width = '1/4';

    public function ranges(): array
    {
        return [
            'TODAY' => __('Today'),
            'YESTERDAY' => __('Yesterday'),
            7 => __('7 Days'),
            30 => __('30 Days'),
            60 => __('60 Days'),
            90 => __('90 Days'),
            180 => __('180 Days'),
            365 => __('1 Year'),
            730 => __('2 Years'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
            'ALL' => 'All Time',
        ];
    }

    public function cacheFor(): Carbon
    {
        return now()->addMinute();
    }
}

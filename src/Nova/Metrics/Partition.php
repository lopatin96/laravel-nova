<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Illuminate\Support\Carbon;
use Laravel\Nova\Metrics\Partition as PartitionMetric;

class Partition extends PartitionMetric
{
    public $width = '1/4';

    public function cacheFor(): Carbon
    {
        return now()->addMinute();
    }

    public function uriKey(): string
    {
        if (isset($this->query)) {
            return parent::uriKey().($this->query ? md5($this->query->toSql()) : '');
        }

        return parent::uriKey();
    }
}

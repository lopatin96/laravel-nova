<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelMail\Models\MailLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;

class MailLogsPerMailType extends Partition
{
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, MailLog::class, 'mail_type');
    }
}

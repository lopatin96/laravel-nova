<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelMail\Models\MailLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\TrendResult;

class MailLogsPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, MailLog::class);
    }
}

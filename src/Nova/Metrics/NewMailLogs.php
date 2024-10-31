<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelMail\Models\MailLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;

class NewMailLogs extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, MailLog::class);
    }
}

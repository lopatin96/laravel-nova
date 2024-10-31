<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelActivitylog\Models\Activitylog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;

class NewActivities extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Activitylog::class);
    }
}

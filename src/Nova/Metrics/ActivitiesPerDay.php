<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\TrendResult;
use Spatie\Activitylog\Models\Activity;

class ActivitiesPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, Activity::class);
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelSubscription\Models\Subscription;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\TrendResult;

class SubscriptionsPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, Subscription::class);
    }
}

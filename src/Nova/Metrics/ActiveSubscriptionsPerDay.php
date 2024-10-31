<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelSubscription\Models\Subscription;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\TrendResult;

class ActiveSubscriptionsPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, Subscription::where('stripe_status', 'active'));
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelSubscription\Models\Subscription;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;

class NewSubscriptions extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Subscription::class);
    }
}

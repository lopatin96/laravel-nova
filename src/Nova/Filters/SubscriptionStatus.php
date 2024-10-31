<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class SubscriptionStatus extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('stripe_status', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Active' => 'active',
            'Canceled' => 'canceled',
        ];
    }
}

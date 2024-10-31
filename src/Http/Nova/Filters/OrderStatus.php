<?php

namespace Atin\LaravelNova\Nova\Filters;

use Atin\LaravelNova\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderStatus extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Incomplete' => 'incomplete',
            'Completed' => 'completed',
            'Processed' => 'processed',
            'Canceled' => 'canceled',
        ];
    }

    public function default(): string
    {
        return 'processed';
    }
}

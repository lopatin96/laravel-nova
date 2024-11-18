<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class ProductStatus extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Design' => 'design',
            'Deployed' => 'deployed',
            'Retired' => 'retired',
        ];
    }

    public function default(): string
    {
        return 'deployed';
    }
}

<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserDevice extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('device', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Desktop' => 'Desktop',
            'Tablet' => 'Tablet',
            'Mobile' => 'Mobile',
        ];
    }
}
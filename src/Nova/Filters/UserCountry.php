<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserCountry extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('country', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Ukraine' => 'ua',
            'Russia' => 'ru',
            'Indonesia' => 'id',
            'India' => 'in',
            'USA' => 'us',
            'Poland' => 'pl',
            'France' => 'fr',
            'Germany' => 'de',
            'Turkey' => 'tr',
            'Kazakhstan' => 'kz',
            'Czech' => 'cz',
        ];
    }
}

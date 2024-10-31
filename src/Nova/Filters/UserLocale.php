<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserLocale extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('locale', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'English' => 'en',
            'Polish' => 'pl',
            'Russian' => 'ru',
            'Ukrainian' => 'uk',
            'French' => 'fr',
            'German' => 'de',
        ];
    }
}

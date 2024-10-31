<?php

namespace Atin\LaravelNova\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserType extends Filter
{
    public function apply(NovaRequest $request, $query, $value): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $value);
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Client' => 'client',
            'Moderator' => 'moderator',
            'Admin' => 'admin',
        ];
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;

class UsersOnlineToday extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, User::whereDate('last_seen_at', now()->today()));
    }

    public function ranges(): array
    {
        return [];
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;

class UsersPerStatus extends Partition
{
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, User::class, 'status')
            ->colors([
                'active' => '#22c55e',
                'restricted' => '#eab308',
                'blocked' => '#ef4444',
            ]);
    }
}

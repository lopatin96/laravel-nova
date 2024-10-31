<?php

namespace Atin\LaravelNova\Nova\Metrics;

use App\Models\User;
use Atin\LaravelSocialAuth\Models\SocialAccount;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;

class UsersPerSocialAccount extends Partition
{
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->result([
            'google' => SocialAccount::where('social_provider', '=', 'google')->count(),
            'facebook' => SocialAccount::where('social_provider', '=', 'facebook')->count(),
            'none' => User::count() - SocialAccount::count(),
        ])
            ->colors([
                'google' => '#ef4444',
                'facebook' => '#2563eb',
                'none' => '#6b7280',
            ]);
    }
}

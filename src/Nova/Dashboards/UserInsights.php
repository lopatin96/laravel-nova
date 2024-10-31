<?php

namespace Atin\LaravelNova\Nova\Dashboards;

use App\Models\User;
use Atin\LaravelNova\Nova\Metrics\UsersPerCountry;
use Atin\LaravelNova\Nova\Metrics\UsersPerDevice;
use Atin\LaravelNova\Nova\Metrics\UsersPerLocale;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Laravel\Nova\Nova;

class UserInsights extends Dashboard
{
    public function name(): string
    {
        return Nova::humanize($this);
    }

    public function cards(): array
    {
        $lastHourUsers = User::where('created_at', '>=', now()->subHour());
        $lastThreeHoursUsers = User::where('created_at', '>=', now()->subHours(3));
        $todayUsers = User::where('created_at', '>=', now()->today());
        $yesterdayUsers = User::where('created_at', '>=', now()->yesterday())->where('created_at', '<=', now()->today());

        return [
            new UsersPerLocale(query: $lastHourUsers, suffixName: 'Last Hour'),
            new UsersPerLocale(query: $lastThreeHoursUsers, suffixName: 'Last 3 Hours'),
            new UsersPerLocale(query: $todayUsers, suffixName: 'Today'),
            new UsersPerLocale(query: $yesterdayUsers, suffixName: 'Yesterday'),

            new UsersPerCountry(query: $lastHourUsers, suffixName: 'Last Hour'),
            new UsersPerCountry(query: $lastThreeHoursUsers, suffixName: 'Last 3 Hours'),
            new UsersPerCountry(query: $todayUsers, suffixName: 'Today'),
            new UsersPerCountry(query: $yesterdayUsers, suffixName: 'Yesterday'),

            new UsersPerDevice(query: $lastHourUsers, suffixName: 'Last Hour'),
            new UsersPerDevice(query: $lastThreeHoursUsers, suffixName: 'Last 3 Hours'),
            new UsersPerDevice(query: $todayUsers, suffixName: 'Today'),
            new UsersPerDevice(query: $yesterdayUsers, suffixName: 'Yesterday'),
        ];
    }
}

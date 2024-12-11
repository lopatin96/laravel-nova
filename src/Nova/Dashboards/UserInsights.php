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
        $todayUsers = User::whereDate('created_at', now()->today());
        $yesterdayUsers = User::whereDate('created_at', now()->yesterday());
        $twoDaysAgoUsers = User::whereDate('created_at', now()->subDays(2));
        $threeDaysAgoUsers = User::whereDate('created_at', now()->subDays(3));

        return [
            new UsersPerLocale(query: $todayUsers, suffixName: 'Today'),
            new UsersPerLocale(query: $yesterdayUsers, suffixName: 'Yesterday'),
            new UsersPerLocale(query: $twoDaysAgoUsers, suffixName: '2 Days ago'),
            new UsersPerLocale(query: $threeDaysAgoUsers, suffixName: '3 Days ago'),

            new UsersPerCountry(query: $todayUsers, suffixName: 'Today'),
            new UsersPerCountry(query: $yesterdayUsers, suffixName: 'Yesterday'),
            new UsersPerCountry(query: $twoDaysAgoUsers, suffixName: '2 Days ago'),
            new UsersPerCountry(query: $threeDaysAgoUsers, suffixName: '3 Days ago'),

            new UsersPerDevice(query: $todayUsers, suffixName: 'Today'),
            new UsersPerDevice(query: $yesterdayUsers, suffixName: 'Yesterday'),
            new UsersPerDevice(query: $twoDaysAgoUsers, suffixName: '2 Days ago'),
            new UsersPerDevice(query: $threeDaysAgoUsers, suffixName: '3 Days ago'),
        ];
    }
}

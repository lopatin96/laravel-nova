<?php

namespace Atin\LaravelNova\Nova\Dashboards;

use Atin\LaravelCashierShop\Models\Order;
use Atin\LaravelNova\Nova\Metrics\IncompleteOrders;
use Atin\LaravelNova\Nova\Metrics\PaidOrders;
use Atin\LaravelNova\Nova\Metrics\PaidOrdersPerCountry;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Laravel\Nova\Nova;

class OrderInsights extends Dashboard
{
    public function name(): string
    {
        return Nova::humanize($this);
    }

    public function cards(): array
    {
        $todayOrders = Order::withTrashed()
            ->whereDate('created_at', now()->today());

        $yesterdayOrders = Order::withTrashed()
            ->whereDate('created_at', now()->yesterday());

        $twoDaysAgoOrders = Order::withTrashed()
            ->whereDate('created_at', now()->subDays(2));

        $threeDaysAgoOrders = Order::withTrashed()
            ->whereDate('created_at', now()->subDays(3));

        return [
            new PaidOrders(query: $todayOrders, suffixName: 'Today'),
            new PaidOrders(query: $yesterdayOrders, suffixName: 'Yesterday'),
            new PaidOrders(query: $twoDaysAgoOrders, suffixName: '2 Days ago'),
            new PaidOrders(query: $threeDaysAgoOrders, suffixName: '3 Days ago'),

            new IncompleteOrders(query: $todayOrders, suffixName: 'Today'),
            new IncompleteOrders(query: $yesterdayOrders, suffixName: 'Yesterday'),
            new IncompleteOrders(query: $twoDaysAgoOrders, suffixName: '2 Days ago'),
            new IncompleteOrders(query: $threeDaysAgoOrders, suffixName: '3 Days ago'),

            new PaidOrdersPerCountry(query: $todayOrders, suffixName: 'Today'),
            new PaidOrdersPerCountry(query: $yesterdayOrders, suffixName: 'Yesterday'),
            new PaidOrdersPerCountry(query: $twoDaysAgoOrders, suffixName: '2 Days ago'),
            new PaidOrdersPerCountry(query: $threeDaysAgoOrders, suffixName: '3 Days ago'),
        ];
    }
}

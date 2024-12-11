<?php

namespace Atin\LaravelNova\Nova\Dashboards;

use Atin\LaravelCashierShop\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Atin\LaravelNova\Nova\Metrics\IncompleteOrders;
use Atin\LaravelNova\Nova\Metrics\PaidOrders;
use Atin\LaravelNova\Nova\Metrics\PaidOrdersPerCountry;
use Atin\LaravelNova\Nova\Metrics\UsersPurchasePercentage;
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

        $todayUsersPurchasePercentage = DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.country, COUNT(DISTINCT orders.user_id) as users_with_orders, COUNT(users.id) as total_users')
            ->whereDate('users.created_at', now()->today())
            ->whereDate('orders.created_at', now()->today())
            ->groupBy('users.country');

        $yesterdayUsersPurchasePercentage = DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.country, COUNT(DISTINCT orders.user_id) as users_with_orders, COUNT(users.id) as total_users')
            ->whereDate('users.created_at', now()->yesterday())
            ->whereDate('orders.created_at', now()->yesterday())
            ->groupBy('users.country');

        $twoDaysAgoUsersPurchasePercentage = DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.country, COUNT(DISTINCT orders.user_id) as users_with_orders, COUNT(users.id) as total_users')
            ->whereDate('users.created_at', now()->subDays(2))
            ->whereDate('orders.created_at', now()->subDays(2))
            ->groupBy('users.country');

        $threeDaysAgoUsersPurchasePercentage = DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.country, COUNT(DISTINCT orders.user_id) as users_with_orders, COUNT(users.id) as total_users')
            ->whereDate('users.created_at', now()->subDays(3))
            ->whereDate('orders.created_at', now()->subDays(3))
            ->groupBy('users.country');

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

            new UsersPurchasePercentage(query: $todayUsersPurchasePercentage, suffixName: 'Today'),
            new UsersPurchasePercentage(query: $yesterdayUsersPurchasePercentage, suffixName: 'Yesterday'),
            new UsersPurchasePercentage(query: $twoDaysAgoUsersPurchasePercentage, suffixName: '2 Days ago'),
            new UsersPurchasePercentage(query: $threeDaysAgoUsersPurchasePercentage, suffixName: '3 Days ago'),
        ];
    }
}

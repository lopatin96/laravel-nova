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

        if (DB::getDriverName() === 'mysql') {
            $todayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() THEN orders.user_id END) * 100.0
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE()'))
                ->groupBy('users.country');

            $yesterdayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() - INTERVAL 1 DAY AND orders.created_at < CURDATE() THEN orders.user_id END) * 100.0
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() - INTERVAL 1 DAY AND users.created_at < CURDATE() THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE() - INTERVAL 1 DAY'))
                ->where('users.created_at', '<', DB::raw('CURDATE()'))
                ->groupBy('users.country');

            $twoDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() - INTERVAL 2 DAY AND orders.created_at < CURDATE() - INTERVAL 1 DAY THEN orders.user_id END) * 100.0
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() - INTERVAL 2 DAY AND users.created_at < CURDATE() - INTERVAL 1 DAY THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE() - INTERVAL 2 DAY'))
                ->where('users.created_at', '<', DB::raw('CURDATE() - INTERVAL 1 DAY'))
                ->groupBy('users.country');

            $threeDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() - INTERVAL 3 DAY 
                                        AND orders.created_at < CURDATE() - INTERVAL 2 DAY 
                                        THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() - INTERVAL 3 DAY 
                                        AND users.created_at < CURDATE() - INTERVAL 2 DAY 
                                        THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE() - INTERVAL 3 DAY'))
                ->where('users.created_at', '<', DB::raw('CURDATE() - INTERVAL 2 DAY'))
                ->groupBy('users.country');

        } else {
            $todayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now") THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now")'))
                ->groupBy('users.country');

            $yesterdayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-1 day") AND orders.created_at < DATE("now") THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-1 day") AND users.created_at < DATE("now") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-1 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now")'))
                ->groupBy('users.country');

            $twoDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-2 day") AND orders.created_at < DATE("now", "-1 day") THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-2 day") AND users.created_at < DATE("now", "-1 day") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-2 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now", "-1 day")'))
                ->groupBy('users.country');

            $threeDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-3 day") AND orders.created_at < DATE("now", "-2 day") THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-3 day") AND users.created_at < DATE("now", "-2 day") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-3 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now", "-2 day")'))
                ->groupBy('users.country');
        }

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

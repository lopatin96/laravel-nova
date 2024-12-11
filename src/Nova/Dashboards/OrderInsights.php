<?php

namespace Atin\LaravelNova\Nova\Dashboards;

use Atin\LaravelCashierShop\Models\Order;
use App\Models\User;
use Atin\LaravelNova\Nova\Metrics\RevenueByCountry;
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
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() AND orders.status = "processed" THEN orders.user_id END) * 100.0
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE()'))
                ->groupBy('users.country');

            $yesterdayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() - INTERVAL 1 DAY AND orders.created_at < CURDATE() AND orders.status = "processed" THEN orders.user_id END) * 100.0
                    / COUNT(DISTINCT CASE WHEN users.created_at >= CURDATE() - INTERVAL 1 DAY AND users.created_at < CURDATE() THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('CURDATE() - INTERVAL 1 DAY'))
                ->where('users.created_at', '<', DB::raw('CURDATE()'))
                ->groupBy('users.country');

            $twoDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= CURDATE() - INTERVAL 2 DAY AND orders.created_at < CURDATE() - INTERVAL 1 DAY AND orders.status = "processed" THEN orders.user_id END) * 100.0
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
                                        AND orders.status = "processed"
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
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now") AND orders.status = "processed" THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now")'))
                ->groupBy('users.country');

            $yesterdayUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-1 day") AND orders.created_at < DATE("now") AND orders.status = "processed" THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-1 day") AND users.created_at < DATE("now") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-1 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now")'))
                ->groupBy('users.country');

            $twoDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-2 day") AND orders.created_at < DATE("now", "-1 day") AND orders.status = "processed" THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-2 day") AND users.created_at < DATE("now", "-1 day") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-2 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now", "-1 day")'))
                ->groupBy('users.country');

            $threeDaysAgoUsersPurchasePercentage = User::select('users.country')
                ->selectRaw('ROUND(
                    COUNT(DISTINCT CASE WHEN orders.created_at >= DATE("now", "-3 day") AND orders.created_at < DATE("now", "-2 day") AND orders.status = "processed" THEN orders.user_id END) * 100.0 
                    / COUNT(DISTINCT CASE WHEN users.created_at >= DATE("now", "-3 day") AND users.created_at < DATE("now", "-2 day") THEN users.id END), 2
                ) AS purchase_percentage')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '>=', DB::raw('DATE("now", "-3 day")'))
                ->where('users.created_at', '<', DB::raw('DATE("now", "-2 day")'))
                ->groupBy('users.country');
        }

        if (DB::getDriverName() === 'mysql') {
            $todayRevenueByCountryUsersRegisteredThisDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->today())
                ->whereDate('users.created_at', now()->today())
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $yesterdayRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->yesterday())
                ->whereDate('users.created_at', now()->yesterday())
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $twoDaysAgoRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(2))
                ->whereDate('users.created_at', now()->subDays(2))
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $threeDaysAgoRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(3))
                ->whereDate('users.created_at', now()->subDays(3))
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));
        } else {
            $todayRevenueByCountryUsersRegisteredThisDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->today())
                ->whereDate('users.created_at', now()->today())
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $yesterdayRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->yesterday())
                ->whereDate('users.created_at', now()->yesterday())
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $twoDaysAgoRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(2))
                ->whereDate('users.created_at', now()->subDays(2))
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $threeDaysAgoRevenueByCountryUsersRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(3))
                ->whereDate('users.created_at', now()->subDays(3))
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));
        }

        if (DB::getDriverName() === 'mysql') {
            $todayRevenueByCountryUsersNotRegisteredThisDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->today())
                ->whereDate('users.created_at', '!=', now()->today())
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $yesterdayRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->yesterday())
                ->whereDate('users.created_at', '!=', now()->yesterday())
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $twoDaysAgoRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(2))
                ->whereDate('users.created_at', '!=', now()->subDays(2))
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));

            $threeDaysAgoRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency')) AS currency"),
                    DB::raw("SUM(CAST(JSON_EXTRACT(orders.log, '$.amount') AS DECIMAL(10, 2))) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(3))
                ->whereDate('users.created_at', '!=', now()->subDays(3))
                ->groupBy('users.country', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.log, '$.currency'))"));
        } else {
            $todayRevenueByCountryUsersNotRegisteredThisDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->today())
                ->whereDate('users.created_at', '!=', now()->today())
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $yesterdayRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->yesterday())
                ->whereDate('users.created_at', '!=', now()->yesterday())
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $twoDaysAgoRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(2))
                ->whereDate('users.created_at', '!=', now()->subDays(2))
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));

            $threeDaysAgoRevenueByCountryUsersNotRegisteredThatDay = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.country',
                    DB::raw("json_extract(orders.log, '$.currency') AS currency"),
                    DB::raw("SUM(CAST(json_extract(orders.log, '$.amount') AS REAL)) AS total_amount")
                )
                ->where('orders.status', 'processed')
                ->whereDate('orders.created_at', now()->subDays(3))
                ->whereDate('users.created_at', '!=', now()->subDays(3))
                ->groupBy('users.country', DB::raw("json_extract(orders.log, '$.currency')"));
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

            new UsersPurchasePercentage(query: $todayUsersPurchasePercentage, suffixName: '[users registered this day] Today'),
            new UsersPurchasePercentage(query: $yesterdayUsersPurchasePercentage, suffixName: '[users registered that day] Yesterday'),
            new UsersPurchasePercentage(query: $twoDaysAgoUsersPurchasePercentage, suffixName: '[users registered that day] 2 Days ago'),
            new UsersPurchasePercentage(query: $threeDaysAgoUsersPurchasePercentage, suffixName: '[users registered that day] 3 Days ago'),

            new RevenueByCountry(query: $todayRevenueByCountryUsersRegisteredThisDay, suffixName: '[users registered this day] Today'),
            new RevenueByCountry(query: $yesterdayRevenueByCountryUsersRegisteredThatDay, suffixName: '[users registered that day] Yesterday'),
            new RevenueByCountry(query: $twoDaysAgoRevenueByCountryUsersRegisteredThatDay, suffixName: '[users registered that day] 2 Days ago'),
            new RevenueByCountry(query: $threeDaysAgoRevenueByCountryUsersRegisteredThatDay, suffixName: '[users registered that day] 3 Days ago'),

            new RevenueByCountry(query: $todayRevenueByCountryUsersNotRegisteredThisDay, suffixName: '[users NOT registered this day] Today'),
            new RevenueByCountry(query: $yesterdayRevenueByCountryUsersNotRegisteredThatDay, suffixName: '[users NOT registered that day] Yesterday'),
            new RevenueByCountry(query: $twoDaysAgoRevenueByCountryUsersNotRegisteredThatDay, suffixName: '[users NOT registered that day] 2 Days ago'),
            new RevenueByCountry(query: $threeDaysAgoRevenueByCountryUsersNotRegisteredThatDay, suffixName: '[users NOT registered that day] 3 Days ago'),
        ];
    }
}

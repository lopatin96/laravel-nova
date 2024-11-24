<?php

namespace Atin\LaravelNova\Nova;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelCashierShop\Models\Order as OrderModel;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Illuminate\Support\Str;
use Khalin\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Order extends Resource
{
    public static string $model = OrderModel::class;

    public static $search = [
        'id', 'user.name', 'user.email',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            LaravelNovaHelper::getUserField($this->user),

            BelongsTo::make('Product')
                ->peekable()
                ->readonly()
                ->displayUsing(fn ($product) => Str::limit($product->name, 32, '…')),

            Status::make('Status')
                ->loadingWhen(['incomplete'])
                ->failedWhen(['canceled'])
                ->sortable(),

            Select::make('Status')->options([
                'incomplete' => 'Incomplete',
                'completed' => 'Completed',
                'canceled' => 'Canceled',
            ])
                ->onlyOnForms(),

            KeyValue::make('Log')
                ->rules('json'),

            Stack::make('Created At', [
                DateTime::make('Created At'),

                Line::make(null, function () {
                    return "({$this->created_at->diffForHumans()})";
                })
                    ->asSmall(),
            ])
                ->sortable()
                ->readonly(),

            Stack::make('Updated At', [
                DateTime::make('Created At'),

                Line::make(null, function () {
                    return "({$this->updated_at->diffForHumans()})";
                })
                    ->asSmall(),
            ])
                ->sortable()
                ->readonly(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        $todayOrders = \Atin\LaravelCashierShop\Models\Order::withTrashed()
            ->whereDate('created_at', now()->today());

        return [
            new Metrics\OrdersPerDay,
            new Metrics\PaidOrdersPerDay,
            new Metrics\PaidOrders(query: $todayOrders),
            new Metrics\IncompleteOrders(query: $todayOrders),
            new Metrics\PaidOrdersPerLocale(query: $todayOrders),
            new Metrics\PaidOrdersPerCountry(query: $todayOrders),
            new Metrics\PaidOrdersPerDevice(query: $todayOrders),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
            new Filters\OrderStatus,
        ];
    }
}

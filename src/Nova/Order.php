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

            Stack::make('User', [
                BelongsTo::make('User')
                    ->peekable()
                    ->readonly()
                    ->displayUsing(fn ($user) => Str::limit($user->name, 20, '…')),

                Line::make(null, function () {
                    return $this->user?->email
                        ? Str::limit($this->user->email, 20, '…')
                        : 'No email';
                }),

                Indicator::make(null, function () {
                    return $this->user?->isOnline() ? 'Online ' : ($this->user?->last_seen_at ? $this->user->last_seen_at->diffForHumans(short: true).' ' : 'Offline');
                })
                    ->shouldHide('Offline')
                    ->colors(['Online ' => 'green'])
                    ->withoutLabels(),

                LaravelNovaHelper::getBillingShoppingStatusIndicator($this->user),

                Line::make(null, function () {
                    $result = '';

                    if ($this->user?->locale) {
                        $result .= $result ? ' · '.$this->user->locale : $this->user->locale;
                    }

                    if ($this->user?->country) {
                        $result .= $result ? ' · '.$this->user->country : $this->user->country;
                    }

                    return $result;
                }),

                Line::make(null, function () {
                    $documents = \Illuminate\Support\Number::format($this->user?->documents->count() ?? 0);
                    $toolContents = \Illuminate\Support\Number::format($this->user?->toolContents->count() ?? 0);

                    return "D.: $documents; T.: $toolContents";
                }),

                Line::make(null, function () {
                    return "Created: {$this->user?->created_at->diffForHumans()}";
                }),
            ])
                ->sortable(),

            BelongsTo::make('Product')
                ->peekable()
                ->readonly()
                ->displayUsing(fn ($product) => Str::limit($product->name, 32, '…')),

            Number::make('Quantity')
                ->sortable()
                ->readonly(),

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

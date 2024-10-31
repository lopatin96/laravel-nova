<?php

namespace Atin\LaravelNova\Nova;

use App\Models\User;
use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Atin\LaravelSubscription\Models\Subscription as SubscriptionModel;
use Illuminate\Support\Str;
use Khalin\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Subscription extends Resource
{
    public static string $model = SubscriptionModel::class;

    public static $title = 'stripe_id';

    public static $search = [
        'id', 'stripe_id', 'user.name', 'user.email',
    ];

    public static $globallySearchable = true;

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            LaravelNovaHelper::getUserField($this->user),

            Status::make('Status', 'stripe_status')
                ->loadingWhen(['incomplete'])
                ->failedWhen(['canceled', 'past_due'])
                ->sortable(),

            Select::make('Status', 'stripe_status')->options([
                'active' => 'Active',
                'incomplete' => 'Incomplete',
                'canceled' => 'Canceled',
                'past_due' => 'Past due',
            ])
                ->onlyOnForms(),

            Stack::make('Ends At', [
                DateTime::make('Ends At'),

                Line::make(null, function () {
                    return $this->ends_at ? "({$this->ends_at->diffForHumans()})" : null;
                })
                    ->asSmall(),
            ])
                ->sortable()
                ->readonly(),

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
        $usersWithActiveSubscriptions = User::whereRelation('stripeSubscription', 'stripe_status', 'active');

        return [
            new Metrics\SubscriptionsPerDay,
            new Metrics\ActiveSubscriptionsPerDay,
            new Metrics\UsersPerLocale(query: $usersWithActiveSubscriptions),
            new Metrics\UsersPerCountry(query: $usersWithActiveSubscriptions),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
            new Filters\SubscriptionStatus,
        ];
    }
}

<?php

namespace Atin\LaravelNova\Nova;

use App\Models\User;
use Atin\LaravelCashierShop\Enums\OrderStatus;
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

            Stack::make('User', [
                BelongsTo::make('User')
                    ->peekable()
                    ->nullable()
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

                Indicator::make(null, function () {
                    $billed = $this->user?->subscribed;
                    $shopped = $this->user?->orders()->status(OrderStatus::Processed)->exists();

                    if ($billed && $shopped) {
                        return 'Billed ('.$this->user?->getSubscribedPlanName().' / '.Str::limit($this->user?->getSubscribedPlanPriceType(), 1, '').') & Shopped';
                    }

                    $billing = $this->user?->billing_visited_at && $this->user?->stripe_id;
                    $shopping = $this->user?->orders()->status(OrderStatus::Incomplete)->exists();

                    if ($billed && $shopping) {
                        return 'Billed ('.$this->user?->getSubscribedPlanName().' / '.Str::limit($this->user?->getSubscribedPlanPriceType(), 1, '').') & Shopping';
                    }

                    if ($billing && $shopped) {
                        return 'Billing & Shopped';
                    }

                    $bill = $this->user?->billing_visited_at;
                    $shop = $this->user?->shop_visited_at;

                    if ($billed && $shop) {
                        return 'Billed ('.$this->user?->getSubscribedPlanName().' / '.Str::limit($this->user?->getSubscribedPlanPriceType(), 1, '').') & Shop';
                    }

                    if ($bill && $shopped) {
                        return 'Bill & Shopped';
                    }

                    if ($billed) {
                        return 'Billed ('.$this->user?->getSubscribedPlanName().' / '.Str::limit($this->user?->getSubscribedPlanPriceType(), 1, '').')';
                    }

                    if ($shopped) {
                        return 'Shopped';
                    }

                    if ($billing && $shopping) {
                        return 'Billing & Shopping';
                    }

                    if ($billing && $shop) {
                        return 'Billing & Shop';
                    }

                    if ($bill && $shopping) {
                        return 'Bill & Shopping';
                    }

                    if ($billing) {
                        return 'Billing';
                    }

                    if ($shopping) {
                        return 'Shopping';
                    }

                    if ($bill && $shop) {
                        return 'Bill & Shop';
                    }

                    if ($bill) {
                        return 'Bill';
                    }

                    if ($shop) {
                        return 'Shop';
                    }

                    return '';
                })
                    ->shouldHide('')
                    ->colors([
                        'Shop' => 'yellow',
                        'Bill' => 'yellow',
                        'Bill & Shop' => 'yellow',

                        'Shopping' => 'orange',
                        'Billing' => 'orange',
                        'Bill & Shopping' => 'orange',
                        'Billing & Shop' => 'orange',
                        'Billing & Shopping' => 'orange',

                        'Shopped' => 'green',
                        'Billed (Pro / m)' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Billed (Pro / y)' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Bill & Shopped' => 'green',
                        'Billed (Pro / m) & Shop' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Billed (Pro / y) & Shop' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Billing & Shopped' => 'green',
                        'Billed (Pro / m) & Shopping' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Billed (Pro / y) & Shopping' => $this->user?->stripeSubscription?->ends_at ? 'red' : 'green',
                        'Billed (Pro / m) & Shopped' => $this->user?->stripeSubscription?->ends_at ? 'purple' : 'black',
                        'Billed (Pro / y) & Shopped' => $this->user?->stripeSubscription?->ends_at ? 'purple' : 'black',
                    ])
                    ->withoutLabels(),

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

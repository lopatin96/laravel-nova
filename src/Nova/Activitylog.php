<?php

namespace Atin\LaravelNova\Nova;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelNova\LaravelNovaHelper;
use Illuminate\Support\Str;
use Khalin\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Activitylog extends Resource
{
    public static string $model = \Atin\LaravelActivitylog\Models\Activitylog::class;

    public static $search = [
        'description', 'user.name', 'user.email',
    ];

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
                        : ' ';
                }),

                Indicator::make(null, function () {
                    return $this->user?->isOnline() ? 'Online ' : ($this->user?->last_seen_at ? $this->user->last_seen_at->diffForHumans(short: true).' ' : 'Offline');
                })
                    ->shouldHide('Offline')
                    ->colors(['Online ' => 'green'])
                    ->withoutLabels(),

                LaravelNovaHelper::getBillingShoppingStatusIndicator($this->user),

                $this->user
                    ? Line::make(null, function () {
                        $result = '';

                        if ($this->user?->locale) {
                            $result .= $result ? ' · '.$this->user->locale : $this->user->locale;
                        }

                        if ($this->user?->country) {
                            $result .= $result ? ' · '.$this->user->country : $this->user->country;
                        }

                        return $result;
                    })
                    : Line::make(null, fn () => ' '),

                $this->user
                    ? Line::make(null, function () {
                        $documents = \Illuminate\Support\Number::format($this->user->documents->count());
                        $toolContents = \Illuminate\Support\Number::format($this->user->toolContents->count());

                        return "D.: $documents; T.: $toolContents";
                    })
                    : Line::make(null, fn () => ' '),

                $this->user
                    ? Line::make(null, function () {
                        return "Created: {$this->user?->created_at->diffForHumans()}";
                    })
                    : Line::make(null, fn () => ' '),

            ])
                ->sortable(),

            Text::make('Description')
                ->sortable()
                ->readonly(),

            Stack::make('Created At', [
                DateTime::make('Created At'),

                Text::make('User', function () {
                    return "({$this->created_at->diffForHumans()})";
                })
                    ->asHtml(),
            ])
                ->sortable()
                ->readonly(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            new Metrics\ActivitiesPerDay,
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
        ];
    }
}

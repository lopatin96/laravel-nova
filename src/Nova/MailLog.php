<?php

namespace Atin\LaravelNova\Nova;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
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

class MailLog extends Resource
{
    public static string $model = \Atin\LaravelMail\Models\MailLog::class;

    public static $search = [
        'id', 'mail_type', 'user.name', 'user.email',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            LaravelNovaHelper::getUserField($this->user),

            Text::make('Mail', 'mail_type')
                ->readonly()
                ->sortable(),

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
            new Metrics\MailLogsPerDay,
            new Metrics\MailLogsPerMailType,
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
        ];
    }
}

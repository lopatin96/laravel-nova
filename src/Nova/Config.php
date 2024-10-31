<?php

namespace Atin\LaravelNova\Nova;

use App\Enums\ConfigCategory;
use Atin\LaravelConfigurator\Enums\ConfigType;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Config extends Resource
{
    public static string $model = \Atin\LaravelConfigurator\Models\Config::class;

    public static $title = 'key';

    public static $search = [
        'id', 'key', 'value',
    ];

    public static $perPageOptions = [
        100,
        200,
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            Text::make('Key')
                ->sortable()
                ->readonly(),

            Select::make('Category')->options([
                ConfigCategory::Platform->value => ConfigCategory::Platform->value,
                ConfigCategory::Documents->value => ConfigCategory::Documents->value,
                ConfigCategory::Analysis->value => ConfigCategory::Analysis->value,
                ConfigCategory::Reports->value => ConfigCategory::Reports->value,
                ConfigCategory::Tools->value => ConfigCategory::Tools->value,
            ])
                ->sortable()
                ->readonly(),

            Text::make('Title')
                ->rules('nullable', 'max:64')
                ->sortable()
                ->hideFromIndex(),

            match ($this->type) {
                ConfigType::ArrayOfStrings => Text::make('Value')->displayUsing(fn () => Str::limit($this->value, 50, '…'))->onlyOnIndex(),
                default => Textarea::make('Value')->hide()->hideFromDetail(),
            },

            match ($this->type) {
                ConfigType::Integer => Number::make('Value'),
                ConfigType::Float => Number::make('Value')->step(0.01),
                ConfigType::Boolean => Boolean::make('Value'),
                ConfigType::ArrayOfStrings => Textarea::make('Value')->alwaysShow(),
                default => Text::make('Value')->displayUsing(fn () => Str::limit($this->value, 50, '…')),
            },

            Select::make('Type')->options([
                ConfigType::String->value => ConfigType::String->value,
                ConfigType::Integer->value => ConfigType::Integer->value,
                ConfigType::Float->value => ConfigType::Float->value,
                ConfigType::Boolean->value => ConfigType::Boolean->value,
                ConfigType::ArrayOfStrings->value => ConfigType::ArrayOfStrings->value,
                ConfigType::ArrayOfIntegers->value => ConfigType::ArrayOfIntegers->value,
            ])
                ->sortable(),

            Text::make('Description')
                ->rules('nullable', 'max:256')
                ->hideFromIndex(),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
        ];
    }
}

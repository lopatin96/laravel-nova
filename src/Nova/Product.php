<?php

namespace Atin\LaravelNova\Nova;

use Atin\LaravelCashierShop\Enums\ProductStatus;
use Atin\LaravelCashierShop\Models\Product as ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Product extends Resource
{
    public static string $model = ProductModel::class;

    public static $title = 'hashid';

    public static $search = [
        'id', 'hashid', 'price_id', 'name', 'model',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            Text::make('Hashid')
                ->sortable()
                ->hideWhenCreating()
                ->readonly(),

            Text::make('Price Id')
                ->hideFromIndex(),

            Text::make('Category')
                ->onlyOnIndex()
                ->displayUsing(fn ($category) => Str::limit($category, 15, '…'))
                ->sortable(),

            Text::make('Category')
                ->hideFromIndex(),

            Text::make('Name')
                ->onlyOnIndex()
                ->displayUsing(fn ($name) => Str::limit($name, 25, '…'))
                ->sortable(),

            Text::make('Name')
                ->hideFromIndex(),

            Image::make('Image')
                ->disk('s3')
                ->path('products')
                ->thumbnail(function ($image) {
                    return $image
                        ? Storage::disk('s3')
                            ->temporaryUrl($image, now()->addMinute())
                        : null;
                })
                ->preview(function ($image) {
                    return $image
                        ? Storage::disk('s3')
                            ->temporaryUrl($image, now()->addMinute())
                        : null;
                }),

            Text::make('Model')
                ->hideFromIndex(),

            Status::make('Status')
                ->loadingWhen(['design'])
                ->failedWhen(['retired'])
                ->sortable(),

            Number::make('Sort Order'),

            Select::make('Status')->options([
                'design' => 'Design',
                'deployed' => 'Deployed',
                'retired' => 'Retired',
            ])
                ->onlyOnForms(),

            KeyValue::make('Prices')
                ->rules('json'),

            KeyValue::make('Crossed Prices')
                ->rules('json'),

            KeyValue::make('Properties')
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

    public function actions(NovaRequest $request): array
    {
        return [
            (new Actions\DeployProduct)->canRun(function ($request, $model) {
                return $model->status !== ProductStatus::Deployed;
            }),
            (new Actions\RetireProduct)->canRun(function ($request, $model) {
                return $model->status !== ProductStatus::Retired;
            }),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
            new Filters\ProductStatus,
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return true;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return true;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return true;
    }
}

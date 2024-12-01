<?php

namespace Atin\LaravelNova\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\Filters\DateRangeFilter;

class Post extends Resource
{
    public static string $model = \Atin\LaravelBlog\Models\Post::class;

    public static $search = [
        'title',
    ];

    public static function relatableUsers(NovaRequest $request, $query)
    {
        $bloggerIds = DB::table('role_user')
            ->where('role_id', DB::table('role_permission')
                ->where('permission_slug', 'create posts')
                ->first()
                ->role_id
            )
            ->get()
            ->pluck('user_id');

        return $query->whereIn('id', $bloggerIds);
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->sortable(),

            BelongsTo::make('User')
                ->displayUsing(fn ($user) => Str::limit($user->name, 20, '…')),

            URL::make('Open', fn () => '/blog/'.$this->slug),

            Text::make('Title')
                ->sortable()
                ->displayUsing(fn () => Str::limit($this->title, 32, '…')),

            Text::make('Slug')
                ->hideFromIndex(),

            Trix::make('Body'),

            Image::make('Image')
                ->disk('s3')
                ->path('posts/'.date('Y/m/d'))
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

            Text::make('Image Alt')
                ->hideFromIndex(),

            Text::make('Meta title')
                ->hideFromIndex(),

            Text::make('Meta description')
                ->hideFromIndex(),

            Boolean::make('Published'),

            Text::make('Geo'),

            Number::make('Views')
                ->sortable()
                ->readonly(),

            Stack::make('Last View At', [
                DateTime::make('Last View At'),

                Line::make(null, function () {
                    return $this->last_view_at
                        ? "({$this->last_view_at->diffForHumans()})"
                        : null;
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
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            new Metrics\PostsPerDay,
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
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

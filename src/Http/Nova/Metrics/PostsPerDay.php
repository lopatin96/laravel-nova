<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelBlog\Models\Post;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\TrendResult;

class PostsPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, Post::class);
    }
}

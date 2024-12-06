<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelBlog\Models\Post;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class PostsPerGeo extends Partition
{
    public function __construct($component = null, ?Builder $query = null, ?string $suffixName = null)
    {
        parent::__construct($component);

        if ($suffixName) {
            $this->name = Nova::humanize($this)." ($suffixName)";
        }

        $this->query = $query;
    }

    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, $this->query ?? Post::class, 'geo')
            ->label(fn ($value) => LaravelNovaHelper::getCountryList()[$value] ?? '—')
            ->colors(LaravelNovaHelper::getCountryColors());
    }
}

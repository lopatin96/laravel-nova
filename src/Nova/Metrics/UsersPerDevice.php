<?php

namespace Atin\LaravelNova\Nova\Metrics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class UsersPerDevice extends Partition
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
        return $this->count($request, $this->query ?? User::class, 'device')
            ->label(fn ($value) => match ($value) {
                'Desktop' => 'Desktop',
                'Tablet' => 'Tablet',
                'Mobile' => 'Mobile',
                default => '—'
            })
            ->colors([
                'Desktop' => '#3b82f6',
                'Tablet' => '#ef4444',
                'Mobile' => '#22c55e',
            ]);
    }
}

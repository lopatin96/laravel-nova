<?php

namespace Atin\LaravelNova\Nova\Metrics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class UsersPerLocale extends Partition
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
        return $this->count($request, $this->query ?? User::class, 'locale')
            ->label(fn ($value) => match ($value) {
                'en' => 'English',
                'pl' => 'Polish',
                'ru' => 'Russian',
                'uk' => 'Ukrainian',
                'fr' => 'French',
                'de' => 'German',
                'tr' => 'Turkish',
                default => '—'
            })
            ->colors([
                'en' => '#ef4444',
                'pl' => '#dc143c',
                'ru' => '#2563eb',
                'uk' => '#ffdd00',
                'fr' => '#002654',
                'de' => '#000000',
                'tr' => '#ff0000',
            ]);
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Nova;

class UsersPurchasePercentage extends Partition
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
        $percentages = $this->query->get()->mapWithKeys(function ($item) {
            return [$item->country => $item->purchase_percentage];
        });

        $filteredPercentages = $percentages->filter(function ($percentage) {
            return $percentage > 0; // Оставляем только проценты больше 0
        });

        return $this->result($filteredPercentages->toArray());
    }
}

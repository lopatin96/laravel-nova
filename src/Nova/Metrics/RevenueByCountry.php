<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Query\Builder;
use Laravel\Nova\Nova;
use Atin\LaravelCashierShop\Helpers\CurrencyHelper;

class RevenueByCountry extends Partition
{
    private string $targetCurrency;

    public function __construct($component = null, ?Builder $query = null, ?string $suffixName = null)
    {
        parent::__construct($component);

        $this->targetCurrency = env('GOOGLE_ADS_TARGET_CURRENCY', 'PLN');

        if ($suffixName) {
            $this->name = Nova::humanize($this) . " [$this->targetCurrency] ($suffixName)";
        }

        $this->query = $query;
    }

    public function calculate(NovaRequest $request): PartitionResult
    {
        $results = $this->query->get()->mapWithKeys(function ($item) {
            return [$item->country => CurrencyHelper::convertAmount($item->total_amount, $item->currency, $this->targetCurrency) / 100];
        });

        return $this->result($results->toArray())
            ->colors(LaravelNovaHelper::getCountryColors());
    }
}

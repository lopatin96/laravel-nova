<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelCashierShop\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class IncompleteOrders extends Partition
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
        return $this->count($request, $this->query ? $this->query->where('user_id', '!=', 1)->where('status', OrderStatus::Incomplete) : Order::where('user_id', '!=', 1)->where('status', OrderStatus::Incomplete), 'product_id')
            ->label(fn ($value) => match ($value) {
                1 => '1 Extra Check',
                2 => '10 Extra Checks',
                3 => '100K Extra Tokens',
                4 => '1M Extra Tokens',
                5 => 'Explicit Text',
                6 => 'Very Large Documents',
                7 => 'Maximum Checking Speed',
                8 => 'Increased Document Storage',
                9 => 'Increased Simultaneous Checks',
                10 => 'Maximum Generation Speed',
                11 => 'Increased Simultaneous Generations',
                12 => '5 Extra Checks',
                13 => '1 Subscribed Extra Check',
                14 => '5 Subscribed Extra Checks',
                15 => '100 Extra Checks',
                16 => 'Complete Url Access',
                default => ucfirst($value)
            });
    }
}

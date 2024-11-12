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
        $products = \Atin\LaravelCashierShop\Models\Product::all()->pluck('name', 'id')->toArray();

        return $this->count(
            $request,
            $this->query
                ? $this->query->where('user_id', '!=', 2)->where('status', OrderStatus::Incomplete)
                : Order::where('user_id', '!=', 1)->where('status', OrderStatus::Incomplete),
            'product_id'
        )
            ->label(fn ($value) => $products[$value] ?? ucfirst($value));
    }
}

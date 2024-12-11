<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelCashierShop\Models\Order;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class PaidOrdersPerCountry extends Partition
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
        $orders = $this->query->with('user')->where('user_id', '!=', 1)->whereIn('status', [OrderStatus::Completed, OrderStatus::Processed])
            ?? Order::withTrashed()->with('user')->where('user_id', '!=', 1)->whereIn('status', [OrderStatus::Completed, OrderStatus::Processed]);

        return $this->result($orders->get()->map(fn ($order) => $order->user->country)->countBy()->toArray())
            ->colors(LaravelNovaHelper::getCountryColors());
    }
}

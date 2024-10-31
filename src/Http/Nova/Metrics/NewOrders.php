<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelCashierShop\Models\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;

class NewOrders extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Order::class);
    }
}

<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelCashierShop\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class PaidOrdersPerLocale extends Partition
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

        return $this->result($orders->get()->map(fn ($order) => $order->user->locale)->countBy()->toArray())
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

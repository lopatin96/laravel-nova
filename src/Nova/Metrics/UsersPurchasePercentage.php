<?php

namespace Atin\LaravelNova\Nova\Metrics;

use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Query\Builder;
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
            $usersWithOrders = $item->users_with_orders;
            $totalUsers = $item->total_users;
            $percentage = $totalUsers > 0 ? round(($usersWithOrders / $totalUsers) * 100, 2) : 0;

            return [$item->country => $percentage];
        });

        return $this->result($percentages->toArray());
    }
}

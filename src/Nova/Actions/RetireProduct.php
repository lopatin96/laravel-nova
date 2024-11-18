<?php

namespace Atin\LaravelNova\Nova\Actions;

use App\Enums\DomainStatus;
use Atin\LaravelCashierShop\Enums\ProductStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class RetireProduct extends Action
{
    use InteractsWithQueue, Queueable;

    public function handle(ActionFields $fields, Collection $models): Collection
    {
        foreach ($models as $model) {
            if ($model->status === ProductStatus::Retired) {
                continue;
            }

            $model->update([
                'status' => ProductStatus::Retired,
            ]);
        }

        return $models;
    }
}

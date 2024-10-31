<?php

namespace Atin\LaravelNova\Helpers;

use App\Models\User;
use Atin\LaravelNova\Nova\User as LaravelNovaUser;
use Atin\LaravelCashierShop\Enums\OrderStatus;
use Khalin\Fields\Indicator;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Text;

class LaravelNovaHelper
{
    public static function getBillingShoppingStatusIndicator(User|LaravelNovaUser|null $user): Indicator|Text
    {
        if (is_null($user)) {
            return Text::make('');
        }

        return Indicator::make(null, function ($user) {
            $billed = $user->subscribed;
            $shopped = $user->orders()->status(OrderStatus::Processed)->exists();

            if ($billed && $shopped) {
                return 'Billed ('.$user->getSubscribedPlanName().' / '.Str::limit($user->getSubscribedPlanPriceType(), 1, '').') & Shopped';
            }

            $billing = $user->billing_visited_at && $user->stripe_id;
            $shopping = $user->orders()->status(OrderStatus::Incomplete)->exists();

            if ($billed && $shopping) {
                return 'Billed ('.$user->getSubscribedPlanName().' / '.Str::limit($user->getSubscribedPlanPriceType(), 1, '').') & Shopping';
            }

            if ($billing && $shopped) {
                return 'Billing & Shopped';
            }

            $bill = $user->billing_visited_at;
            $shop = $user->shop_visited_at;

            if ($billed && $shop) {
                return 'Billed ('.$user->getSubscribedPlanName().' / '.Str::limit($user->getSubscribedPlanPriceType(), 1, '').') & Shop';
            }

            if ($bill && $shopped) {
                return 'Bill & Shopped';
            }

            if ($billed) {
                return 'Billed ('.$user->getSubscribedPlanName().' / '.Str::limit($user->getSubscribedPlanPriceType(), 1, '').')';
            }

            if ($shopped) {
                return 'Shopped';
            }

            if ($billing && $shopping) {
                return 'Billing & Shopping';
            }

            if ($billing && $shop) {
                return 'Billing & Shop';
            }

            if ($bill && $shopping) {
                return 'Bill & Shopping';
            }

            if ($billing) {
                return 'Billing';
            }

            if ($shopping) {
                return 'Shopping';
            }

            if ($bill && $shop) {
                return 'Bill & Shop';
            }

            if ($bill) {
                return 'Bill';
            }

            if ($shop) {
                return 'Shop';
            }

            return '';
        })
            ->shouldHide('')
            ->colors([
                'Shop' => 'yellow',
                'Bill' => 'yellow',
                'Bill & Shop' => 'yellow',

                'Shopping' => 'orange',
                'Billing' => 'orange',
                'Bill & Shopping' => 'orange',
                'Billing & Shop' => 'orange',
                'Billing & Shopping' => 'orange',

                'Shopped' => 'green',
                'Billed (Pro / m)' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Billed (Pro / y)' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Bill & Shopped' => 'green',
                'Billed (Pro / m) & Shop' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Billed (Pro / y) & Shop' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Billing & Shopped' => 'green',
                'Billed (Pro / m) & Shopping' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Billed (Pro / y) & Shopping' => $user->stripeSubscription?->ends_at ? 'red' : 'green',
                'Billed (Pro / m) & Shopped' => $user->stripeSubscription?->ends_at ? 'purple' : 'black',
                'Billed (Pro / y) & Shopped' => $user->stripeSubscription?->ends_at ? 'purple' : 'black',
            ])
            ->withoutLabels();
    }
}

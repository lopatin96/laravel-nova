<?php

namespace Atin\LaravelNova\Helpers;

use App\Models\User;
use Atin\LaravelNova\Nova\User as LaravelNovaUser;
use Atin\LaravelCashierShop\Enums\OrderStatus;
use Illuminate\Support\Number;
use Khalin\Fields\Indicator;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Line;

class LaravelNovaHelper
{
    public static function getUserField(User|LaravelNovaUser|null $user): Stack
    {
        $stripeSubscriptionEndsAt = $user?->stripeSubscription?->ends_at;

        return Stack::make('User', [
            BelongsTo::make('User')
                ->peekable()
                ->nullable()
                ->readonly()
                ->displayUsing(fn ($user) => Str::limit($user->name, 20, '…')),

            Line::make(null, static function () use ($user) {
                $result = '#' . Number::format($user->id);

                if ($user?->email) {
                    $result .= ' · ' . Str::limit($user->email, 15, '…');
                }

                return $result;
            }),

            Indicator::make(null, static function () use ($user) {
                return $user?->isOnline()
                    ? 'Online '
                    : (
                    $user?->last_seen_at
                        ? $user->last_seen_at->diffForHumans(short: true).' '
                        : 'Offline'
                    );
            })
                ->shouldHide('Offline')
                ->colors(['Online ' => 'green'])
                ->withoutLabels(),

            static::getBillingShoppingStatusIndicator($user),

            $user
                ? Line::make(null, static function () use ($user) {
                $result = '';

                if ($user?->locale) {
                    $result .= $result ? ' · '.$user->locale : $user->locale;
                }

                if ($user?->country) {
                    $result .= $result ? ' · '.$user->country : $user->country;
                }

                if ($user?->device) {
                    $result .= $result ? ' · '.$user->device : $user->device;
                }

                if ($prompt = $user?->getNovaPrompt()) {
                    $result .= $result ? ' · '.$prompt : $prompt;
                }

                return Str::lower($result);
            })
                : Line::make(null, static fn () => ' '),

            $user
                ? Line::make(null, static function () use ($user) {
                $result = "C: {$user?->created_at->diffForHumans(short: true)}";

                if ($user->referrer_id) {
                    $result .= $result ? ' (ref_id: #'.$user->referrer_id.')' : $user->locale;
                }

                return $result;
            })
                : Line::make(null, static fn () => ' '),

        ])
            ->sortable();
    }

    public static function getBillingShoppingStatusIndicator(User|LaravelNovaUser|null $user): Indicator|Text
    {
        if (is_null($user)) {
            return Text::make('');
        }

        $stripeSubscriptionEndsAt = $user?->stripeSubscription?->ends_at;

        return Indicator::make(null, function () use ($user) {
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
                'Billed (Pro / m)' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Billed (Pro / y)' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Bill & Shopped' => 'green',
                'Billed (Pro / m) & Shop' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Billed (Pro / y) & Shop' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Billing & Shopped' => 'green',
                'Billed (Pro / m) & Shopping' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Billed (Pro / y) & Shopping' => $stripeSubscriptionEndsAt ? 'red' : 'green',
                'Billed (Pro / m) & Shopped' => $stripeSubscriptionEndsAt ? 'purple' : 'black',
                'Billed (Pro / y) & Shopped' => $stripeSubscriptionEndsAt ? 'purple' : 'black',
            ])
            ->withoutLabels();
    }
}

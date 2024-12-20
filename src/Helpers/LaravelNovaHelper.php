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
                $result = '';

                if ($user) {
                    $result = '#' . Number::format($user->id);
                }

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

                    if ($user?->country) {
                        $result .= $result ? ' · '.$user->country : $user->country;
                    }

                    if ($user?->locale) {
                        $result .= $result ? ' · '.$user->locale : $user->locale;
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
                        $result .= ' (ref. id: #'.Number::format($user->referrer_id).')';
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

    public static function getCountryList(): array
    {
        return [
            'af' => 'Afghanistan',
            'al' => 'Albania',
            'dz' => 'Algeria',
            'as' => 'American Samoa',
            'ad' => 'Andorra',
            'ao' => 'Angola',
            'ai' => 'Anguilla',
            'aq' => 'Antarctica',
            'ag' => 'Antigua and Barbuda',
            'ar' => 'Argentina',
            'am' => 'Armenia',
            'aw' => 'Aruba',
            'au' => 'Australia',
            'at' => 'Austria',
            'az' => 'Azerbaijan',
            'bs' => 'Bahamas',
            'bh' => 'Bahrain',
            'bd' => 'Bangladesh',
            'bb' => 'Barbados',
            'by' => 'Belarus',
            'be' => 'Belgium',
            'bz' => 'Belize',
            'bj' => 'Benin',
            'bm' => 'Bermuda',
            'bt' => 'Bhutan',
            'bo' => 'Bolivia',
            'ba' => 'Bosnia and Herzegovina',
            'bw' => 'Botswana',
            'bv' => 'Bouvet Island',
            'br' => 'Brazil',
            'bq' => 'British Antarctic Territory',
            'io' => 'British Indian Ocean Territory',
            'vg' => 'British Virgin Islands',
            'bn' => 'Brunei',
            'bg' => 'Bulgaria',
            'bf' => 'Burkina Faso',
            'bi' => 'Burundi',
            'kh' => 'Cambodia',
            'cm' => 'Cameroon',
            'ca' => 'Canada',
            'ct' => 'Canton and Enderbury Islands',
            'cv' => 'Cape Verde',
            'ky' => 'Cayman Islands',
            'cf' => 'Central African Republic',
            'td' => 'Chad',
            'cl' => 'Chile',
            'cn' => 'China',
            'cx' => 'Christmas Island',
            'cc' => 'Cocos [Keeling] Islands',
            'co' => 'Colombia',
            'km' => 'Comoros',
            'cg' => 'Congo - Brazzaville',
            'cd' => 'Congo - Kinshasa',
            'ck' => 'Cook Islands',
            'cr' => 'Costa Rica',
            'hr' => 'Croatia',
            'cu' => 'Cuba',
            'cy' => 'Cyprus',
            'cz' => 'Czech Republic',
            'ci' => 'Côte d’Ivoire',
            'dk' => 'Denmark',
            'dj' => 'Djibouti',
            'dm' => 'Dominica',
            'do' => 'Dominican Republic',
            'nq' => 'Dronning Maud Land',
            'dd' => 'East Germany',
            'ec' => 'Ecuador',
            'eg' => 'Egypt',
            'sv' => 'El Salvador',
            'gq' => 'Equatorial Guinea',
            'er' => 'Eritrea',
            'ee' => 'Estonia',
            'et' => 'Ethiopia',
            'fk' => 'Falkland Islands',
            'fo' => 'Faroe Islands',
            'fj' => 'Fiji',
            'fi' => 'Finland',
            'fr' => 'France',
            'gf' => 'French Guiana',
            'pf' => 'French Polynesia',
            'tf' => 'French Southern Territories',
            'fq' => 'French Southern and Antarctic Territories',
            'ga' => 'Gabon',
            'gm' => 'Gambia',
            'ge' => 'Georgia',
            'de' => 'Germany',
            'gh' => 'Ghana',
            'gi' => 'Gibraltar',
            'gr' => 'Greece',
            'gl' => 'Greenland',
            'gd' => 'Grenada',
            'gp' => 'Guadeloupe',
            'gu' => 'Guam',
            'gt' => 'Guatemala',
            'gg' => 'Guernsey',
            'gn' => 'Guinea',
            'gw' => 'Guinea-Bissau',
            'gy' => 'Guyana',
            'ht' => 'Haiti',
            'hm' => 'Heard Island and McDonald Islands',
            'hn' => 'Honduras',
            'hk' => 'Hong Kong SAR China',
            'hu' => 'Hungary',
            'is' => 'Iceland',
            'in' => 'India',
            'id' => 'Indonesia',
            'ir' => 'Iran',
            'iq' => 'Iraq',
            'ie' => 'Ireland',
            'im' => 'Isle of Man',
            'il' => 'Israel',
            'it' => 'Italy',
            'jm' => 'Jamaica',
            'jp' => 'Japan',
            'je' => 'Jersey',
            'jt' => 'Johnston Island',
            'jo' => 'Jordan',
            'kz' => 'Kazakhstan',
            'ke' => 'Kenya',
            'ki' => 'Kiribati',
            'kw' => 'Kuwait',
            'kg' => 'Kyrgyzstan',
            'la' => 'Laos',
            'lv' => 'Latvia',
            'lb' => 'Lebanon',
            'ls' => 'Lesotho',
            'lr' => 'Liberia',
            'ly' => 'Libya',
            'li' => 'Liechtenstein',
            'lt' => 'Lithuania',
            'lu' => 'Luxembourg',
            'mo' => 'Macau SAR China',
            'mk' => 'Macedonia',
            'mg' => 'Madagascar',
            'mw' => 'Malawi',
            'my' => 'Malaysia',
            'mv' => 'Maldives',
            'ml' => 'Mali',
            'mt' => 'Malta',
            'mh' => 'Marshall Islands',
            'mq' => 'Martinique',
            'mr' => 'Mauritania',
            'mu' => 'Mauritius',
            'yt' => 'Mayotte',
            'fx' => 'Metropolitan France',
            'mx' => 'Mexico',
            'fm' => 'Micronesia',
            'mi' => 'Midway Islands',
            'md' => 'Moldova',
            'mc' => 'Monaco',
            'mn' => 'Mongolia',
            'me' => 'Montenegro',
            'ms' => 'Montserrat',
            'ma' => 'Morocco',
            'mz' => 'Mozambique',
            'mm' => 'Myanmar [Burma]',
            'na' => 'Namibia',
            'nr' => 'Nauru',
            'np' => 'Nepal',
            'nl' => 'Netherlands',
            'an' => 'Netherlands Antilles',
            'nt' => 'Neutral Zone',
            'nc' => 'New Caledonia',
            'nz' => 'New Zealand',
            'ni' => 'Nicaragua',
            'ne' => 'Niger',
            'ng' => 'Nigeria',
            'nu' => 'Niue',
            'nf' => 'Norfolk Island',
            'kp' => 'North Korea',
            'vd' => 'North Vietnam',
            'mp' => 'Northern Mariana Islands',
            'no' => 'Norway',
            'om' => 'Oman',
            'pc' => 'Pacific Islands Trust Territory',
            'pk' => 'Pakistan',
            'pw' => 'Palau',
            'ps' => 'Palestinian Territories',
            'pa' => 'Panama',
            'pz' => 'Panama Canal Zone',
            'pg' => 'Papua New Guinea',
            'py' => 'Paraguay',
            'yd' => 'People\'s Democratic Republic of Yemen',
            'pe' => 'Peru',
            'ph' => 'Philippines',
            'pn' => 'Pitcairn Islands',
            'pl' => 'Poland',
            'pt' => 'Portugal',
            'pr' => 'Puerto Rico',
            'qa' => 'Qatar',
            'ro' => 'Romania',
            'ru' => 'Russia',
            'rw' => 'Rwanda',
            're' => 'Réunion',
            'bl' => 'Saint Barthélemy',
            'sh' => 'Saint Helena',
            'kn' => 'Saint Kitts and Nevis',
            'lc' => 'Saint Lucia',
            'mf' => 'Saint Martin',
            'pm' => 'Saint Pierre and Miquelon',
            'vc' => 'Saint Vincent and the Grenadines',
            'ws' => 'Samoa',
            'sm' => 'San Marino',
            'sa' => 'Saudi Arabia',
            'sn' => 'Senegal',
            'rs' => 'Serbia',
            'cs' => 'Serbia and Montenegro',
            'sc' => 'Seychelles',
            'sl' => 'Sierra Leone',
            'sg' => 'Singapore',
            'sk' => 'Slovakia',
            'si' => 'Slovenia',
            'sb' => 'Solomon Islands',
            'so' => 'Somalia',
            'za' => 'South Africa',
            'gs' => 'South Georgia and the South Sandwich Islands',
            'kr' => 'South Korea',
            'es' => 'Spain',
            'lk' => 'Sri Lanka',
            'sd' => 'Sudan',
            'sr' => 'Suriname',
            'sj' => 'Svalbard and Jan Mayen',
            'sz' => 'Swaziland',
            'se' => 'Sweden',
            'ch' => 'Switzerland',
            'sy' => 'Syria',
            'st' => 'São Tomé and Príncipe',
            'tw' => 'Taiwan',
            'tj' => 'Tajikistan',
            'tz' => 'Tanzania',
            'th' => 'Thailand',
            'tl' => 'Timor-Leste',
            'tg' => 'Togo',
            'tk' => 'Tokelau',
            'to' => 'Tonga',
            'tt' => 'Trinidad and Tobago',
            'tn' => 'Tunisia',
            'tr' => 'Turkey',
            'tm' => 'Turkmenistan',
            'tc' => 'Turks and Caicos Islands',
            'tv' => 'Tuvalu',
            'um' => 'U.S. Minor Outlying Islands',
            'pu' => 'U.S. Miscellaneous Pacific Islands',
            'vi' => 'U.S. Virgin Islands',
            'ug' => 'Uganda',
            'ua' => 'Ukraine',
            'su' => 'Union of Soviet Socialist Republics',
            'ae' => 'United Arab Emirates',
            'gb' => 'United Kingdom',
            'us' => 'United States',
            'zz' => 'Unknown or Invalid Region',
            'uy' => 'Uruguay',
            'uz' => 'Uzbekistan',
            'vu' => 'Vanuatu',
            'va' => 'Vatican City',
            've' => 'Venezuela',
            'vn' => 'Vietnam',
            'wk' => 'Wake Island',
            'wf' => 'Wallis and Futuna',
            'eh' => 'Western Sahara',
            'ye' => 'Yemen',
            'zm' => 'Zambia',
            'zw' => 'Zimbabwe',
            'ax' => 'Åland Islands',
        ];
    }

    public static function getCountryColors(): array
    {
        return [
            'ua' => '#ffdd00',
            'ru' => '#2563eb',
            'id' => '#f43f5e',
            'in' => '#10b981',
            'us' => '#0a3161',
            'pl' => '#dc143c',
            'fr' => '#002654',
            'de' => '#000000',
            'tr' => '#c90000',
            'kz' => '#00ABC2',
            'cz' => '#11457E',
            'es' => '#FABD01',
            'pt' => '#0F6700',
        ];
    }
}

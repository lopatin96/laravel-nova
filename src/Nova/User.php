<?php

namespace Atin\LaravelNova\Nova;

use App\Enums\ConfigKey;
use App\Nova\Actions\AddAiWritingExtraTokens;
use App\Nova\Actions\AddAntiplagiarismCompleteUrlAccess;
use App\Nova\Actions\AddAntiplagiarismExplicitText;
use App\Nova\Actions\AddAntiplagiarismExtraChecks;
use App\Nova\Actions\AddAntiplagiarismIncreasedDocumentStorage;
use App\Nova\Actions\AddAntiplagiarismVeryLargeDocuments;
use Atin\LaravelCashierShop\Enums\OrderStatus;
use Atin\LaravelNova\Helpers\LaravelNovaHelper;
use Atin\LaravelUserStatuses\Enums\UserStatus;
use Atin\LaravelUserTypes\Enums\UserType;
use Illuminate\Support\Str;
use Khalin\Fields\Indicator;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Marshmallow\Filters\DateRangeFilter;

abstract class User extends Resource
{
    public static string $model = \App\Models\User::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name', 'email', 'stripe_id',
    ];

    public static $globallySearchable = true;

    public static $with = ['socialAccount'];

    public function fields(NovaRequest $request): array
    {
        return $this->getFields($request);
    }

    protected function additionalSubscriptionsFields(): array
    {
        return [
            DateTime::make('Billing Visited At', 'billing_visited_at')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->nullable()
                ->readonly(),

            Text::make('Stripe Id', 'stripe_id')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->nullable(),

            Boolean::make('Billed', 'subscribed')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->readonly(),

            DateTime::make('Shop Visited At', 'shop_visited_at')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->nullable()
                ->readonly(),

            Boolean::make('Shopped')
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->trueValue($this->orders()->status(OrderStatus::Processed)->exists()),
        ];
    }

    protected function additionalDetailsFields(NovaRequest $request): array
    {
        return [
            Text::make('Locale')
                ->hideWhenUpdating()
                ->readonly(),

            Text::make('Country')
                ->nullable(),

            Text::make('Device')
                ->nullable(),
        ];
    }

    protected function configFields(): array
    {
        return [
            KeyValue::make('Config')
                ->rules('json'),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        $todayUsers = \App\Models\User::where('created_at', '>=', now()->today());

        return [
            new Metrics\UsersPerDay,
            new Metrics\UsersPerCountry(query: $todayUsers, suffixName: 'Today'),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new DateRangeFilter('created_at', 'Created Date'),
            new Filters\UserType,
            new Filters\UserStatus,
            new Filters\UserLocale,
            new Filters\UserCountry,
            new Filters\UserDevice,
        ];
    }

    public function getFields(NovaRequest $request): array
    {
        return array_merge(
            [
                ID::make()
                    ->sortable(),

                Stack::make('User', [
                    Email::make('Email')
                        ->onlyOnIndex()
                        ->displayUsing(fn () => Str::limit($this->email, 20, '…'))
                        ->sortable(),

                    Indicator::make(null, function () {
                        return $this->isOnline() ? 'Online ' : ($this->last_seen_at ? $this->last_seen_at->diffForHumans(short: true).' ' : 'Offline');
                    })
                        ->shouldHide('Offline')
                        ->colors(['Online ' => 'green'])
                        ->withoutLabels(),

                    LaravelNovaHelper::getBillingShoppingStatusIndicator($this),
                ])
                    ->onlyOnIndex()
                    ->sortable(),

                Email::make('Email')
                    ->hideFromIndex()
                    ->rules('nullable', 'email', 'max:254')
                    ->creationRules('unique:users,email')
                    ->updateRules('unique:users,email,{{resourceId}}'),

                Text::make('Name')
                    ->displayUsing(fn () => Str::limit($this->name, 20, '…'))
                    ->sortable()
                    ->onlyOnIndex(),

                Text::make('Name')
                    ->rules('required', 'max:255')
                    ->hideFromIndex(),

                Avatar::make('Profile Photo', 'profile_photo_path')
                    ->disk('s3')
                    ->path('profile-photos')
                    ->indexWidth(50)
                    ->detailWidth(200)
                    ->squared()
                    ->hideFromIndex(),

                Select::make('Type')->options([
                    UserType::Client->value => UserType::Client->name,
                    UserType::Business->value => UserType::Business->name,
                    UserType::Moderator->value => UserType::Moderator->name,
                ])
                    ->sortable()
                    ->hideFromIndex(),

                Select::make('Status')->options([
                    UserStatus::Active->value => UserStatus::Active->name,
                    UserStatus::Restricted->value => UserStatus::Restricted->name,
                    UserStatus::Blocked->value => UserStatus::Blocked->name,
                ])
                    ->sortable()
                    ->onlyOnForms(),

                Indicator::make('Status')
                    ->hideFromIndex()
                    ->labels([
                        UserStatus::Active->value => UserStatus::Active->name,
                        UserStatus::Restricted->value => UserStatus::Restricted->name,
                        UserStatus::Blocked->value => UserStatus::Blocked->name,
                    ])
                    ->colors([
                        UserStatus::Active->value => 'green',
                        UserStatus::Restricted->value => 'orange',
                        UserStatus::Blocked->value => 'red',
                    ]),

                Text::make('Social Provider', 'socialAccount.social_provider')
                    ->hideWhenUpdating()
                    ->nullable()
                    ->hideFromIndex(),

                new Panel('Subscription', $this->additionalSubscriptionsFields()),

                new Panel('Additional Details', $this->additionalDetailsFields($request)),

                new Panel('Platform-Specific Data', $this->getPlatformSpecificFields()),

                HasMany::make('Orders'),

                Boolean::make('Online', 'last_seen_at')
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->trueValue($this->isOnline()),

                DateTime::make('Trial Ends At')
                    ->sortable()
                    ->hideFromIndex()
                    ->hideFromDetail(),

                Stack::make('Created At', [
                    DateTime::make('Created At'),

                    Text::make('User', function () {
                        return "({$this->created_at->diffForHumans()})";
                    })
                        ->asHtml(),

                    Line::make('User', function () {
                        return "Trial ends: {$this->trial_ends_at->diffForHumans()}";
                    })
                        ->asSmall(),
                ])
                    ->readonly(),

                new Panel('Config', $this->configFields()),
            ],

            $this->getPlatformSpecificRelations(),

            [
                BelongsToMany::make('Roles', 'roles', \Pktharindu\NovaPermissions\Nova\Role::class),

                HasMany::make('Activity Logs'),

                HasMany::make('Mail Logs'),
            ]
        );
    }

    abstract public function getPlatformSpecificFields(): array;

    abstract public function getPlatformSpecificRelations(): array;
}

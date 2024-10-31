<?php

namespace Atin\LaravelNova\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;
use Pktharindu\NovaPermissions\Checkboxes;
use Pktharindu\NovaPermissions\Role as RoleModel;

class Role extends NovaResource
{
    public static string $model = RoleModel::class;

    public static function group()
    {
        return __(config('nova-permissions.role_resource_group', 'Other'));
    }

    public static $title = 'name';

    public static $search = [
        'name',
    ];

    public static $with = [
        'users',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required')
                ->sortable(),

            Slug::make(__('Slug'), 'slug')
                ->from('name')
                ->rules('required')
                ->creationRules('unique:'.config('nova-permissions.table_names.roles', 'roles'))
                ->updateRules('unique:'.config('nova-permissions.table_names.roles', 'roles').',slug,{{resourceId}}')
                ->sortable()
                ->hideFromIndex(),

            Checkboxes::make(__('Permissions'), 'permissions')
                ->withGroups()
                ->options(collect(config('nova-permissions.permissions'))->map(function ($permission, $key) {
                    return [
                        'group' => __($permission['group']),
                        'option' => $key,
                        'label' => __($permission['display_name']),
                        'description' => __($permission['description']),
                    ];
                })
                    ->groupBy('group')
                    ->toArray()),

            Text::make(__('Users'), function () {
                return \count($this->users);
            })
                ->onlyOnIndex(),

            DateTime::make('Created At')
                ->withFriendlyDate()
                ->hideWhenCreating(),

            BelongsToMany::make(__('Users'), 'users', config('nova-permissions.user_resource', 'App\Nova\User'))
                ->searchable(),
        ];
    }

    public static function label(): \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator|\Illuminate\Contracts\Foundation\Application|null
    {
        return __('Roles');
    }

    public static function singularLabel(): \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator|\Illuminate\Contracts\Foundation\Application|null
    {
        return __('Role');
    }
}

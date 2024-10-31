<?php

namespace Atin\LaravelNova\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    public static $globallySearchable = false;

    public static $showColumnBorders = true;

    public static $showPollingToggle = true;

    public static $perPageOptions = [
        25,
        50,
        100,
        200,
    ];

    public static function indexQuery(NovaRequest $request, $query): mixed
    {
        return $query;
    }

    public static function scoutQuery(NovaRequest $request, $query): mixed
    {
        return $query;
    }

    public static function detailQuery(NovaRequest $request, $query): mixed
    {
        return parent::detailQuery($request, $query);
    }

    public static function relatableQuery(NovaRequest $request, $query): mixed
    {
        return parent::relatableQuery($request, $query);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
}

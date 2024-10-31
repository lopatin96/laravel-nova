# Install
In Config Resource you should implement `getConfigCategories()` method. Example:
```php
public function getConfigCategories(): array
{
    return [
        ConfigCategory::Platform->value => ConfigCategory::Platform->value,
        ConfigCategory::Documents->value => ConfigCategory::Documents->value,
        ConfigCategory::Analysis->value => ConfigCategory::Analysis->value,
        ConfigCategory::Reports->value => ConfigCategory::Reports->value,
        ConfigCategory::Tools->value => ConfigCategory::Tools->value,
    ];
}
```

In User Resource you should implement `getPlatformSpecificFields()`, `getPlatformSpecificRelations`, `actions` methods. Example:
```php
public function getPlatformSpecificFields(): array
{
    return [
        Number::make(__('D.'), fn (): string => \Illuminate\Support\Number::format($this->documents->count())),

        Number::make(__('T.'), fn (): string => \Illuminate\Support\Number::format($this->toolContents->count())),
    ];
}

public function getPlatformSpecificRelations(): array
{
    return [
        HasMany::make('Documents'),

        HasMany::make('Tool Contents'),
    ];
}
```
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
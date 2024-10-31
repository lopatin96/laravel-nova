# Install
### Views
Add these lines to `resources/views/auth/login.blade.php`:

```html
<x-laravel-social-auth::socialite-login />
```

and these to `resources/views/auth/register.blade.php`:

```html
<x-laravel-social-auth::socialite-register />
```

### FortifyServiceProvider
Add these lines to `app/Providers/FortifyServiceProvider.php` as uses to manage redirections:

```php
use Atin\LaravelSocialAuth\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
```

then add singletons to **boot** method in `app/Providers/FortifyServiceProvider.php`:

```php
public function boot(): void
{
    …
    $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    $this->app->singleton(TwoFactorLoginResponseContract::class, LoginResponse::class);
}
```

### Trait
Add **HasSocialAccount** trait to User model:

```php

use Atin\LaravelSocialAuth\Traits\HasSocialAccount;

class User extends Authenticatable
{
    use HasSocialAccount, …
```

### Config
Public config:
```php
php artisan vendor:publish --tag="laravel-social-auth-config"
```

and comment/uncomment providers in `config/laravel-social-auth.php`:
```php
return [
    'providers' => [
        'google' => [
            'title' => 'Google',
        ],

        'facebook' => [
            'title' => 'Facebook'
        ],

//        'instagram' => [
//            'title' => 'Instagram'
//        ],
    ],
];
```

### Configuration
Add these keys to `config/services.php` to manage google and facebook authentications:

```json
'google' => [
    'api_key' => env('GOOGLE_API_KEY'),
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT'),
],
```

# Publishing
### Migrations
```php
php artisan vendor:publish --tag="laravel-social-auth-migrations"
```

### Localization
```php
php artisan vendor:publish --tag="laravel-social-auth-lang"
```

### Views
```php
php artisan vendor:publish --tag="laravel-social-auth-views"
```

### Config
```php
php artisan vendor:publish --tag="laravel-social-auth-config"
```
# Laravel Guest Upgrade (Guest-to-User Migration Helper)

## Features
- Works with Laravel 10, 11 & 12
- Assigns a unique guest identifier
- Seamlessly migrates guest-owned models to a real user on registration/login
- Simple API, no deep integration needed

---

## Install
```bash
composer require MattYeend/guest-to-user-helper
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
```

---

## Middleware Registration
Laravel 10 and 11
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \MattYeend\GuestToUserHelper\Http\Middleware\AssignGuestIdentifier::class,
    ],
];
```

Laravel 12
```php
// bootstrap/app.php
use MattYeend\GuestToUserHelper\Http\Middleware\AssignGuestIdentifier;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            AssignGuestIdentifier::class,
        ]);
    })
    ->create();
```

--- 

## Usage
Add `HasGuestOwnership` to any model you want to track for guests:
```php
use MattYeend\GuestToUserHelper\Traits\HasGuestOwnership;

class Cart extends Model
{
    use HasGuestOwnership;
}
```
Migrate data after login/registration:
```php
app(\MattYeend\GuestToUserHelper\GuestMigrator::class)->migrate(auth()->id());
```
Events available:
- `GuestMigrating`
- `GuestMigrated`

--- 

## Testing
Run the included tests:
```bash
vendor/bin/phpunit
```

---

## License
This package is licensed under the MIT License.

---

## Contributing
Feel free to fork the repository and submit pull requests for improvements or new features!
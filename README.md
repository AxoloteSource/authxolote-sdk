# Authxolote SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/axolote-source/authxolote-sdk.svg?style=flat-square)](https://packagist.org/packages/axolote-source/authxolote-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/axolote-source/authxolote-sdk.svg?style=flat-square)](https://packagist.org/packages/axolote-source/authxolote-sdk)

Authxolote SDK is a Laravel library designed to seamlessly integrate your application with the Authxolote authentication and authorization service.

## Installation

You can install the package via composer:

```bash
composer require axolote-source/authxolote-sdk
```

The service provider will automatically register itself.

### Configuration

Publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag="authxolote-config"
```

This will create a `config/authxolote.php` file where you can configure:
- `url`: The Authxolote API base URL.
- `token`: Your application's authentication token.
- `cache`: Enable or disable caching for user data.
- `sync_user`: Whether to look up users in your local database or use API data directly.

You can also use environment variables in your `.env` file:

```env
AUTHXOLOTE_URL=https://api.your-service.com
AUTHXOLOTE_TOKEN=your-app-token
AUTHXOLOTE_CACHE=true
AUTHXOLOTE_SYNC_USER=true
```

## Setup Authentication

To use the Authxolote guard, add it to your `config/auth.php`:

```php
'guards' => [
    'authxolote' => [
        'driver' => 'authxolote',
        'provider' => 'users',
    ],
],
```

## Usage

### Authentication

Once configured, you can use the `authxolote` guard in your routes or controllers:

```php
Route::middleware('auth:authxolote')->get('/user', function (Request $request) {
    return $request->user();
});
```

### Checking Permissions

You can easily check for permissions using the `Authxolote` facade:

```php
use Authxolote\Sdk\Authxolote;

// Check a single permission
if (Authxolote::action('edit-posts')->isAllow()) {
    // User has permission
}

// Check multiple permissions (all must be allowed)
if (Authxolote::actions(['edit-posts', 'delete-posts'])->isAllowAll()) {
    // User has all permissions
}
```

### User Registration

Register new users directly through the SDK:

```php
use Authxolote\Sdk\Authxolote;

$user = Authxolote::register(
    'user@example.com',
    'John Doe',
    'password123',
    'admin' // role key
);
```

### Console Commands

To synchronize roles and actions, you must first create a `config/actions.php` file and register the roles with their respective actions:

```php
return [
    'roles' => [
        'admin' => [
            'app.model.action',
        ],
    ],
];
```

Then, synchronize the roles and actions defined in your configuration:

```bash
php artisan authxolote:actions
```

## Testing

The SDK provides a built-in fake mode to facilitate unit testing without making actual API calls:

```php
use Authxolote\Sdk\Authxolote;

// Fake specific actions
Authxolote::actionsFake(['edit-posts']);

// Now this will return true without calling the API
Authxolote::action('edit-posts')->isAllow(); 

// This will return false
Authxolote::action('other-action')->isAllow();
```

### Middleware

The SDK includes a middleware to protect your routes based on permissions. It is automatically registered as `isAllow`.

#### Basic Usage

You can use it in your routes:

```php
Route::middleware(['auth:authxolote', 'isAllow:edit-posts'])->get('/posts/edit', function () {
    // Only users with 'edit-posts' permission can access this
});
```

#### Controller Groups Example

It is recommended to use a naming convention like `project.module.action` for your permissions. Here is an example using a controller group:

```php
use App\Http\Controllers\TurnController;
use Illuminate\Support\Facades\Route;

Route::controller(TurnController::class)->group(function () {
    Route::post('', 'store')->middleware('isAllow:gob.turn.store');
    Route::get('/{id}', 'show')->middleware('isAllow:gob.turn.show');
    Route::put('/{id}', 'update')->middleware('isAllow:gob.turn.update');
    Route::delete('/{id}', 'delete')->middleware('isAllow:gob.turn.delete');
});
```

If the user does not have permission, the middleware will:
- Return a JSON response with status 403 if the request expects JSON.
- Throw a 403 HTTP exception otherwise.

To use this middleware, your User model (or the object returned by the guard) must use the `Authxolote\Sdk\Traits\HasActions` trait.

## Credits

- [Luis Ozuna](https://github.com/LuisOzParr)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

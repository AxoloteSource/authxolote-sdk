# Authxolote SDK

[![Última versión en Packagist](https://img.shields.io/packagist/v/axolote-source/authxolote-sdk.svg?style=flat-square)](https://packagist.org/packages/axolote-source/authxolote-sdk)
[![Descargas totales](https://img.shields.io/packagist/dt/axolote-source/authxolote-sdk.svg?style=flat-square)](https://packagist.org/packages/axolote-source/authxolote-sdk)

Authxolote SDK es una librería para Laravel diseñada para integrar perfectamente tu aplicación con el servicio de autenticación y autorización de Authxolote.

## Instalación

Puedes instalar el paquete a través de composer:

```bash
composer require axolote-source/authxolote-sdk
```

El service provider se registrará automáticamente.

### Configuración

Publica el archivo de configuración usando el siguiente comando:

```bash
php artisan vendor:publish --tag="authxolote-config"
```

Esto creará un archivo `config/authxolote.php` donde puedes configurar:
- `url`: La URL base de la API de Authxolote.
- `token`: El token de autenticación de tu aplicación.
- `cache`: Habilitar o deshabilitar el almacenamiento en caché de los datos del usuario.
- `sync_user`: Si se deben buscar los usuarios en tu base de datos local o usar los datos de la API directamente.

También puedes usar variables de entorno en tu archivo `.env`:

```env
AUTHXOLOTE_URL=https://api.tu-servicio.com
AUTHXOLOTE_TOKEN=tu-token-de-app
AUTHXOLOTE_CACHE=true
AUTHXOLOTE_SYNC_USER=true
```

## Configuración de Autenticación

Para usar el guard de Authxolote, añádelo a tu archivo `config/auth.php`:

```php
'guards' => [
    'authxolote' => [
        'driver' => 'authxolote',
        'provider' => 'users',
    ],
],
```

## Uso

### Autenticación

Una vez configurado, puedes usar el guard `authxolote` en tus rutas o controladores:

```php
Route::middleware('auth:authxolote')->get('/user', function (Request $request) {
    return $request->user();
});
```

### Verificación de Permisos

Puedes verificar permisos fácilmente usando el facade `Authxolote`:

```php
use Authxolote\Sdk\Authxolote;

// Verificar un solo permiso
if (Authxolote::action('edit-posts')->isAllow()) {
    // El usuario tiene permiso
}

// Verificar múltiples permisos (todos deben estar permitidos)
if (Authxolote::actions(['edit-posts', 'delete-posts'])->isAllowAll()) {
    // El usuario tiene todos los permisos
}
```

### Registro de Usuarios

Registra nuevos usuarios directamente a través del SDK:

```php
use Authxolote\Sdk\Authxolote;

$user = Authxolote::register(
    'user@example.com',
    'John Doe',
    'password123',
    'admin' // clave del rol
);
```

### Console Commands

Para sincronizar los roles y acciones, primero debes crear el archivo `config/actions.php` y registrar los roles con sus respectivas acciones:

```php
return [
    'roles' => [
        'admin' => [
            'app.model.action',
        ],
    ],
];
```

Luego, sincroniza los roles y acciones definidos en tu configuración ejecutando:

```bash
php artisan authxolote:actions
```

## Pruebas (Testing)

El SDK proporciona un modo "fake" integrado para facilitar las pruebas unitarias sin realizar llamadas reales a la API:

```php
use Authxolote\Sdk\Authxolote;

// Simular acciones específicas
Authxolote::actionsFake(['edit-posts']);

// Ahora esto devolverá true sin llamar a la API
Authxolote::action('edit-posts')->isAllow(); 

// Esto devolverá false
Authxolote::action('otra-accion')->isAllow();
```

### Middleware

El SDK incluye un middleware para proteger tus rutas basado en permisos. Se registra automáticamente como `isAllow`.

#### Uso Básico

Puedes usarlo en tus rutas:

```php
Route::middleware(['auth:authxolote', 'isAllow:edit-posts'])->get('/posts/edit', function () {
    // Solo los usuarios con el permiso 'edit-posts' pueden acceder aquí
});
```

#### Ejemplo con Grupos de Controladores

Se recomienda seguir una convención de nombres como `proyecto.modulo.accion` para tus permisos. Aquí tienes un ejemplo usando un grupo de controladores:

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

Si el usuario no tiene permiso, el middleware:
- Devolverá una respuesta JSON con estado 403 si la petición espera JSON.
- Lanzará una excepción HTTP 403 en caso contrario.

Para usar este middleware, tu modelo User (o el objeto devuelto por el guard) debe usar el trait `Authxolote\Sdk\Traits\HasActions`.

## Créditos

- [Luis Ozuna](https://github.com/LuisOzParr)

## Licencia

La Licencia MIT (MIT). Por favor, consulta el [Archivo de Licencia](LICENSE.md) para más información.

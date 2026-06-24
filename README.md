# BlxPHP

Framework PHP ligero para construir APIs REST. Diseñado para ser simple, directo y sin abstracciones innecesarias.

## Requisitos

- PHP >= 8.1
- Apache con `mod_rewrite` habilitado
- Composer

## Instalación

```bash
composer require bleckzdev/blxphp
```

## Inicio rápido

### 1. Estructura del proyecto

```
mi-proyecto/
├── App/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Guards/
│   │   └── AuthGuard.php
│   └── Router.php
├── .env
├── .htaccess
├── composer.json
└── index.php
```

### 2. Punto de entrada (`index.php`)

```php
<?php
require_once 'vendor/autoload.php';

use BlxPHP\Init;
use App\Router;

Init::Bootstrap(Router::routes());
```

### 3. Definir rutas (`App/Router.php`)

```php
<?php
namespace App;

use BlxPHP\Router\Route;
use App\Controllers\UserController;
use App\Guards\AuthGuard;

class Router {
    public static function routes(): array {
        return [
            Route::Get('users', UserController::class, 'index'),
            Route::Get('users/:id', UserController::class, 'show'),
            Route::Post('users', UserController::class, 'store', [AuthGuard::class]),
            Route::Put('users/:id', UserController::class, 'update', [AuthGuard::class]),
            Route::Delete('users/:id', UserController::class, 'destroy', [AuthGuard::class]),
        ];
    }
}
```

### 4. Crear un controller (`App/Controllers/UserController.php`)

```php
<?php
namespace App\Controllers;

use BlxPHP\Request;
use BlxPHP\Responser;
use BlxPHP\Database\Postgres;
use BlxPHP\Database\DatabaseManager;
use BlxPHP\Env;

class UserController {

    public function __construct() {
        DatabaseManager::add('main', new Postgres(
            Env::get('DB_HOST'),
            Env::get('DB_PORT'),
            Env::get('DB_NAME'),
            Env::get('DB_USER'),
            Env::get('DB_PASS')
        ));
    }

    public function index(): void {
        $db = DatabaseManager::get('main');
        $users = $db->FetchAll("SELECT * FROM users");
        Responser::success($users);
    }

    public function show(): void {
        $id = Request::get()['Url_id'];
        $db = DatabaseManager::get('main');
        $user = $db->FetchOne("SELECT * FROM users WHERE id = ?", [$id]);

        if (!$user) {
            Responser::notFound('Usuario no encontrado');
        }

        Responser::success($user);
    }

    public function store(): void {
        $data = Request::json();
        $db = DatabaseManager::get('main');
        $id = $db->Insert(
            "INSERT INTO users (name, email) VALUES (?, ?)",
            [$data['name'], $data['email']]
        );
        Responser::success(['id' => $id], 'Usuario creado');
    }
}
```

### 5. Configurar `.htaccess`

```apache
RewriteEngine On

RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]

RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

### 6. Configurar `composer.json`

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "App/"
        }
    },
    "require": {
        "bleckzdev/blxphp": "^1.0"
    }
}
```

---

## Módulos

### Routing

Métodos disponibles para definir rutas:

```php
use BlxPHP\Router\Route;

Route::Get($path, $controller, $action, $guards);
Route::Post($path, $controller, $action, $guards);
Route::Put($path, $controller, $action, $guards);
Route::Patch($path, $controller, $action, $guards);
Route::Delete($path, $controller, $action, $guards);
```

**Parámetros dinámicos** — usa `:` para definir parámetros en la URL. Se acceden vía `$_GET['Url_nombre']` o `Request::get()['Url_nombre']`:

```php
// Ruta: users/:id/posts/:postId
// URL:  users/5/posts/12
// Resultado: $_GET['Url_id'] = '5', $_GET['Url_postId'] = '12'

Route::Get('users/:id/posts/:postId', PostController::class, 'show');
```

**Guards** — clases que se instancian antes de ejecutar el controller. Útil para autenticación:

```php
Route::Post('users', UserController::class, 'store', [AuthGuard::class]);
```

---

### Request

Acceso a los datos de la petición HTTP:

```php
use BlxPHP\Request;

$queryParams = Request::get();       // $_GET
$formData    = Request::post();      // $_POST
$files       = Request::files();     // $_FILES
$jsonBody    = Request::json();      // JSON del body (php://input)
```

---

### Responser

Respuestas JSON estandarizadas:

```php
use BlxPHP\Responser;

// Respuesta exitosa — 200
Responser::success($data, 'Mensaje opcional');
// { "status": "success", "desc": "Mensaje opcional", "data": [...] }

// Error genérico — 500
Responser::error('Descripción del error');

// Bad Request — 400
Responser::badRequest('Datos inválidos');

// Not Found — 404
Responser::notFound('Recurso no encontrado');

// Unauthorized — 401
Responser::unauthorized('No autorizado');

// No Data — 200 con status error
Responser::noData('Sin resultados');

// Error personalizado
Responser::customError('custom_status', 'Mensaje', 422);

// Debug
Responser::Debug($data);

// Respuestas no-JSON
Responser::csv($csvString);
Responser::image('/path/to/image.jpg');

// JSON crudo con código de estado
Responser::json(['key' => 'value'], 200);
```

---

### Env

Carga y acceso a variables de entorno desde el archivo `.env` en la raíz del proyecto:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=mi_base
DB_USER=admin
DB_PASS=secreto
```

```php
use BlxPHP\Env;

// Se carga automáticamente la primera vez que se usa
$host = Env::get('DB_HOST');  // 'localhost'

// O cargar manualmente
Env::loadEnv();
```

> El `.env` se busca en `$_SERVER['DOCUMENT_ROOT']/.env`. Soporta comentarios con `#`.

---

### Database

Sistema de conexión a bases de datos con soporte para múltiples instancias simultáneas.

#### Drivers disponibles

```php
use BlxPHP\Database\MySQL;
use BlxPHP\Database\Postgres;

// MySQL
$mysql = new MySQL(
    host: 'localhost',
    port: '3306',       // opcional, por defecto 3306
    database: 'mi_db',
    user: 'root',
    password: 'secret'
);

// PostgreSQL
$pg = new Postgres(
    host: 'localhost',
    port: '5432',       // opcional, por defecto 5432
    database: 'mi_db',
    user: 'admin',
    password: 'secret'
);

// PostgreSQL: cambiar schema
$pg->setSchema('public');
```

#### Métodos de consulta

```php
// SELECT múltiple
$users = $db->FetchAll("SELECT * FROM users WHERE active = ?", [true]);

// SELECT uno
$user = $db->FetchOne("SELECT * FROM users WHERE id = ?", [1]);

// INSERT — retorna el ID generado
$id = $db->Insert("INSERT INTO users (name) VALUES (?)", ['Juan']);

// UPDATE / DELETE — retorna true si ejecuta correctamente
$db->Query("UPDATE users SET name = ? WHERE id = ?", ['Pedro', 1]);
$db->Query("DELETE FROM users WHERE id = ?", [5]);
```

#### Transacciones

```php
$db->beginTransaction();
try {
    $db->Insert("INSERT INTO orders (user_id) VALUES (?)", [1]);
    $db->Query("UPDATE inventory SET stock = stock - 1 WHERE product_id = ?", [10]);
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
}
```

#### DatabaseManager

Registro de conexiones nombradas para reutilizarlas en toda la aplicación:

```php
use BlxPHP\Database\DatabaseManager;
use BlxPHP\Database\Postgres;
use BlxPHP\Database\MySQL;

// Registrar conexiones
DatabaseManager::add('main', new Postgres('host', '5432', 'app_db', 'user', 'pass'));
DatabaseManager::add('logs', new MySQL('host2', '3306', 'log_db', 'user', 'pass'));

// Usar en cualquier parte de la aplicación
$db = DatabaseManager::get('main');
$users = $db->FetchAll("SELECT * FROM users");

// Verificar si una conexión existe
DatabaseManager::has('main');    // true

// Listar conexiones registradas
DatabaseManager::list();         // ['main', 'logs']

// Eliminar una conexión
DatabaseManager::remove('logs');
```

#### Acceso directo a PDO

Si necesitas usar PDO directamente:

```php
$pdo = $db->getConnection();
```

---

### Validator

Validación de datos con reglas encadenadas por `|`. Si falla, responde automáticamente con HTTP 400.

#### Uso básico

```php
use BlxPHP\Helpers\Validator;

$data = Request::json();

// Validación simple — verifica que los campos existan
Validator::validate($data, ['nombre', 'email', 'edad']);

// Validación avanzada con reglas
Validator::check($data, [
    'nombre'   => 'required|string|min:2|max:100',
    'edad'     => 'required|int|min_val:1|max_val:120',
    'email'    => 'required|email|max:255',
    'telefono' => 'nullable|string|length:10',
    'monto'    => 'required|numeric|min_val:0.01',
    'status'   => 'required|in:activo,inactivo,pendiente',
    'fecha'    => 'required|date',
    'notas'    => 'nullable|string|max:500',
]);
```

#### Reglas disponibles

| Regla | Descripción |
|---|---|
| `required` | El campo debe existir y no estar vacío |
| `nullable` | Si el campo no existe o es null, se omiten las demás reglas |
| `string` | Debe ser string |
| `int` | Debe ser entero |
| `float` / `numeric` | Debe ser numérico |
| `bool` | Debe ser booleano (`true`/`false`/`1`/`0`) |
| `array` | Debe ser array |
| `email` | Formato de email válido |
| `url` | Formato de URL válido |
| `date` | Fecha válida (`Y-m-d` o `Y-m-d H:i:s`) |
| `alpha` | Solo letras |
| `alpha_num` | Solo letras y números |
| `alpha_spaces` | Solo letras y espacios (incluye acentos) |
| `min:N` | Largo mínimo (string/array) |
| `max:N` | Largo máximo (string/array) |
| `length:N` | Largo exacto |
| `min_val:N` | Valor numérico mínimo |
| `max_val:N` | Valor numérico máximo |
| `between_val:N,M` | Valor numérico entre N y M (inclusive) |
| `in:a,b,c` | El valor debe estar en la lista |
| `not_in:a,b,c` | El valor NO debe estar en la lista |
| `regex:/pattern/` | Debe cumplir la expresión regular |

---

### CORS

Se configura automáticamente en el `Bootstrap`. Permite todos los orígenes y métodos HTTP estándar.

### ErrorHandler

Manejo global de errores. Convierte errores PHP en excepciones y responde con JSON en caso de errores fatales. Se activa automáticamente en el `Bootstrap`.

---

## Docker

Ejemplo de despliegue con Docker:

```dockerfile
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip unzip git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar extensiones según el driver de BD que uses
RUN docker-php-ext-install pdo pdo_pgsql  # PostgreSQL
# RUN docker-php-ext-install pdo pdo_mysql  # MySQL

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

```yaml
# docker-compose.yaml
services:
  app:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
```

## Licencia

MIT

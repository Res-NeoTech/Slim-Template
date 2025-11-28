# Slim Framework Template

A production-ready Slim Framework 4 template with Docker support, featuring PSR-4 autoloading, PHP-View templating, and a clean MVC structure.

## Table of Contents

- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Creating Controllers](#creating-controllers)
- [Creating Views](#creating-views)
- [Adding Routes](#adding-routes)
- [Adding Composer Packages](#adding-composer-packages)
- [Configuration](#configuration)
- [Best Practices](#best-practices)

## Getting Started

### Prerequisites

- Docker & Docker Compose (recommended)
- OR PHP 8.2+ with Composer

### Quick Start with Docker

```bash
# Clone the repository
git clone <your-repo-url>
cd Slim-Template

# Start Docker containers
docker-compose up -d

# Install dependencies
docker-compose exec php composer install

# Access the application
# Open http://localhost:8000 in your browser
```

### Quick Start without Docker

```bash
# Install dependencies
composer install

# Start PHP built-in server
php -S localhost:8000 -t public

# Access the application
# Open http://localhost:8000 in your browser
```

## Project Structure

```
Slim-Template/
├── config/
│   └── web-routes.php          # Route definitions
├── docker/
│   └── nginx/
│       └── default.conf         # Nginx configuration
├── public/
│   └── index.php               # Application entry point
├── src/
│   └── Controllers/
│       └── MainController.php  # Example controller
├── view/
│   ├── layout.php              # Master layout template
│   └── home.php                # Example view
├── vendor/                     # Composer dependencies (auto-generated)
├── .env                        # Environment variables
├── composer.json               # Project dependencies
├── docker-compose.yml          # Docker services configuration
└── Dockerfile                  # PHP container configuration
```

## Installation

### Environment Configuration

Create a `.env` file in the root directory:

```env
APP_PORT=8000
```

The `APP_PORT` variable controls which port the application runs on (default: 8000).

### Install Dependencies

**With Docker:**
```bash
docker-compose exec php composer install
```

**Without Docker:**
```bash
composer install
```

## Creating Controllers

Controllers handle the application logic and are located in `src/Controllers/`.

### Step 1: Create Controller File

Create a new PHP file in `src/Controllers/`, for example `UserController.php`:

```php
<?php
namespace Fauza\Template\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class UserController
{
    /**
     * Display user profile
     */
    public function profile(Request $req, Response $resp, array $args): Response
    {
        $view = new PhpRenderer("../view");
        $view->setLayout("layout.php");

        $data = [
            'title' => 'User Profile',
            'username' => $args['username'] ?? 'Guest'
        ];

        return $view->render($resp, 'user/profile.php', $data);
    }

    /**
     * API endpoint example
     */
    public function getUserData(Request $req, Response $resp, array $args): Response
    {
        $data = [
            'id' => $args['id'],
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $resp->getBody()->write(json_encode($data));
        return $resp->withHeader('Content-Type', 'application/json');
    }
}
```

### Controller Best Practices

- Use PSR-4 namespace: `Fauza\Template\Controllers`
- Each method must accept `Request`, `Response`, and `array $args` parameters
- Always return a `Response` object
- Use `PhpRenderer` for HTML views
- Use `$resp->getBody()->write()` for API responses

## Creating Views

Views are located in the `view/` directory and use plain PHP templates.

### Step 1: Create View File

Create a new PHP file in `view/`, for example `user/profile.php`:

```php
<div class="profile">
    <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
    <p>This is your profile page.</p>
</div>
```

### Using Layouts

The template includes a master layout (`view/layout.php`) that wraps your views.

**Layout structure (view/layout.php):**
```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
</head>
<body>
    <main>
        <?= $content ?>  <!-- Your view content is injected here -->
    </main>
</body>
</html>
```

**Using the layout in a controller:**
```php
$view = new PhpRenderer("../view");
$view->setLayout('layout.php');  // Set the master layout
return $view->render($resp, 'your-view.php', $data);
```

### Creating a Custom Layout

Create a new layout file in `view/`, for example `admin-layout.php`:

```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <nav>Admin Navigation</nav>
    <main><?= $content ?></main>
</body>
</html>
```

Use it in your controller:
```php
$view->setLayout('admin-layout.php');
```

### Rendering Without a Layout

To render a view without a layout (e.g., for AJAX responses):

```php
$view = new PhpRenderer('../view');
// Don't call setLayout()
return $view->render($resp, 'partial-view.php', $data);
```

### View Best Practices

- Always escape output with `htmlspecialchars()` to prevent XSS
- Organize views in subdirectories by feature (e.g., `view/user/`, `view/admin/`)
- Keep logic minimal in views - use controllers for data processing
- Variables passed from controllers are directly available in views

## Adding Routes

Routes are defined in `config/web-routes.php`.

### Basic Routes

```php
<?php
namespace Fauza\Template\Config;

use Fauza\Template\Controllers\MainController;
use Fauza\Template\Controllers\UserController;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// GET route
$app->get('/', [MainController::class, 'home']);

// POST route
$app->post('/user/create', [UserController::class, 'create']);

// PUT route
$app->put('/user/{id}', [UserController::class, 'update']);

// DELETE route
$app->delete('/user/{id}', [UserController::class, 'delete']);
```

### Route Parameters

Capture dynamic URL segments:

```php
// Single parameter
$app->get('/user/{id}', [UserController::class, 'show']);

// Multiple parameters
$app->get('/post/{year}/{month}/{slug}', [PostController::class, 'show']);

// Optional parameters
$app->get('/search[/{query}]', [SearchController::class, 'index']);
```

Access parameters in your controller:
```php
public function show(Request $req, Response $resp, array $args): Response
{
    $userId = $args['id'];
    // Your logic here
}
```

### Route Groups

Organize related routes:

```php
$app->group('/api', function ($group) {
    $group->get('/users', [UserController::class, 'list']);
    $group->get('/users/{id}', [UserController::class, 'getUserData']);
    $group->post('/users', [UserController::class, 'create']);
});
```

### HTTP Methods

Available methods:
- `$app->get()` - GET requests
- `$app->post()` - POST requests
- `$app->put()` - PUT requests
- `$app->delete()` - DELETE requests
- `$app->patch()` - PATCH requests
- `$app->options()` - OPTIONS requests
- `$app->any()` - Any HTTP method
- `$app->map(['GET', 'POST'], ...)` - Specific methods

## Adding Composer Packages

### Step 1: Install Package

**With Docker:**
```bash
docker-compose exec php composer require package/name
```

**Without Docker:**
```bash
composer require package/name
```

### Examples

**Install a database ORM (Eloquent):**
```bash
docker-compose exec php composer require illuminate/database
```

**Install a validation library:**
```bash
docker-compose exec php composer require respect/validation
```

**Install development dependencies:**
```bash
docker-compose exec php composer require --dev phpunit/phpunit
```

### Step 2: Use the Package

Packages are autoloaded automatically. Just import and use them:

```php
<?php
namespace Fauza\Template\Controllers;

use Respect\Validation\Validator as v;

class FormController
{
    public function validate(Request $req, Response $resp, array $args): Response
    {
        $email = $req->getParsedBody()['email'] ?? '';

        if (!v::email()->validate($email)) {
            // Handle validation error
        }

        // Continue processing
    }
}
```

### Common Packages

- **Database:** `illuminate/database` (Eloquent ORM)
- **Validation:** `respect/validation`
- **Environment:** `vlucas/phpdotenv`
- **Authentication:** `tuupola/slim-jwt-auth`
- **Logging:** `monolog/monolog`
- **Testing:** `phpunit/phpunit` (dev)

## Configuration

### Changing the Application Port

Edit `.env`:
```env
APP_PORT=3000
```

Then restart Docker:
```bash
docker-compose down
docker-compose up -d
```

### Adding Middleware

Edit `public/index.php` before the routing section:

```php
// Error middleware
$app->addErrorMiddleware(true, true, true);

// Custom middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('X-Custom-Header', 'Value');
});

// Routing
require_once '../config/web-routes.php';
```

### Database Configuration

Create a database configuration file `config/database.php`:

```php
<?php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'your_database',
    'username'  => 'your_username',
    'password'  => 'your_password',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
```

Include it in `public/index.php`:
```php
require __DIR__ . '/../config/database.php';
```

### Adding Database Service to Docker

Edit `docker-compose.yml` to add MySQL:

```yaml
services:
  # ... existing services ...

  mysql:
    image: mysql:8.0
    container_name: slim_mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: slim_app
      MYSQL_USER: slim_user
      MYSQL_PASSWORD: slim_pass
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - app_network

volumes:
  mysql_data:
```

## Best Practices

### Code Organization

1. **Controllers** - Keep them focused on a single resource
2. **Views** - Use subdirectories to organize by feature
3. **Models** - Create a `src/Models/` directory for database models
4. **Services** - Create a `src/Services/` directory for business logic

### Security

1. Always validate and sanitize user input
2. Use `htmlspecialchars()` when outputting data in views
3. Use prepared statements for database queries
4. Enable error middleware in production with logging
5. Never commit `.env` files with sensitive data

### Performance

1. Use route caching in production
2. Enable OPcache in PHP configuration
3. Minimize database queries
4. Use lazy loading for dependencies

### Development Workflow

1. Make changes to your code
2. Refresh browser - changes are reflected immediately (volumes are mounted)
3. Use `docker-compose logs -f` to view logs
4. Use `docker-compose exec php bash` to access the PHP container

### Debugging

**View logs:**
```bash
docker-compose logs -f php
docker-compose logs -f web
```

**Access PHP container:**
```bash
docker-compose exec php bash
```

**Run Composer commands:**
```bash
docker-compose exec php composer dump-autoload
docker-compose exec php composer update
```

## Additional Resources

- [Slim Framework Documentation](https://www.slimframework.com/)
- [PSR-7 HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/)
- [PHP-View Documentation](https://github.com/slimphp/PHP-View)
- [Docker Documentation](https://docs.docker.com/)
- [Composer Documentation](https://getcomposer.org/doc/)

## License

This template is under the Palms license.

## Credits

Created by FauZaPespi - Best template of all time!

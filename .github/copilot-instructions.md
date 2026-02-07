# AI Coding Agent Instructions for API Monitor

## Project Overview
**Api-Monitoring** is a Laravel 12 application (PHP 8.2+) for monitoring APIs. Key facts:
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vite + Tailwind CSS
- **DB**: default dev/test uses SQLite (`.env.example` and `phpunit.xml`); production often uses MySQL via env vars
- **Tooling**: Composer, npm/vite, `concurrently` for dev orchestration

**Quick commands:** `composer run setup`, `composer run dev`, `composer run test`, `vendor/bin/pint`

## Development Workflow

### Setup
```bash
composer run setup    # Installs dependencies, generates APP_KEY, runs migrations, builds frontend
```

### Running the Application
```bash
composer run dev      # Starts concurrent development stack
```
The `dev` script uses `npx concurrently` to run these processes:
- `php artisan serve` (app server)
- `php artisan queue:listen --tries=1` (background jobs)
- `php artisan pail --timeout=0` (live logs)
- `npm run dev` (Vite dev server)

Use `composer run dev` locally to reproduce developer environment (logs + queue processing + hot reload).

### Testing & Quality
```bash
composer run test     # Clears config cache, runs PHPUnit tests (Unit + Feature suites)
vendor/bin/pint       # PHP code style fixer (Laravel standard)
```
Tests use SQLite in-memory database and synchronous queue for speed. See `phpunit.xml` for test environment config.

## Project Structure & Patterns

### Laravel Architecture
- **Routes**: `routes/web.php` - All HTTP routes (currently minimal with welcome page)
- **Models**: `app/Models/User.php` - Eloquent ORM models with `HasFactory`, `Notifiable` traits
- **Controllers**: `app/Http/Controllers/` - Request handlers (empty, ready for expansion)
- **Factories**: `database/factories/UserFactory.php` - Model factory for seeding/testing
- **Migrations**: `database/migrations/` - Schema changes (users, cache, jobs tables included)
- **Config**: `config/` - Application configuration files (use `env()` helper for .env values)

### Key Conventions
1. **Namespace Pattern**: Use PSR-4 autoloading
   - App classes: `App\Models`, `App\Http\Controllers`
   - Tests: `Tests\Feature`, `Tests\Unit`
2. **Model Traits**: Always include `HasFactory` for models with factories
3. **Mass Assignment**: Explicitly define `$fillable` arrays in models (security)
4. **Attribute Casting**: Use `casts()` method for type safety (e.g., `password => hashed`)
5. **Authentication**: User model extends `Authenticatable` with password hashing

### Frontend (Vite + Tailwind)
- Entry points: `resources/css/app.css`, `resources/js/app.js`
- Tailwind CSS integrated via `@tailwindcss/vite` plugin
- Vite watches for changes in `resources/` and auto-rebuilds
- Compiled output served from `public/` directory

## Critical Configuration

### Environment Variables (`.env`)
- **Database**: `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`
- **App**: `APP_NAME=API_Monitor`, `APP_DEBUG=true` (local only), `APP_URL=http://localhost`
- **Queue**: `QUEUE_CONNECTION=database` (uses DB for job storage)
- **Cache**: `CACHE_STORE=database`
- **Session**: `SESSION_DRIVER=database`
- **Logging**: `LOG_LEVEL=debug` (development setting)

### Database
- Uses migrations for schema management
- Jobs, cache, and sessions stored in database (not filesystem)
- SQLite used in test environment only

## Common Development Tasks

### Creating New Features
1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName -m` (with migration)
3. Create controller: `php artisan make:controller ControllerName`
4. Add routes in `routes/web.php` using `Route::get()`, `Route::post()`, etc.

### Database Operations
```bash
php artisan migrate              # Run pending migrations
php artisan migrate:rollback     # Revert last batch
php artisan seed                 # Run seeders
php artisan tinker              # Interactive shell (similar to Rails console)
```

### Code Quality
- Run `vendor/bin/pint` before committing (fixes PSR-12 style automatically)
- Follow Laravel conventions: camelCase for variables, PascalCase for classes, snake_case for database columns

## Testing Approach
- **Unit Tests**: `tests/Unit/` - Test isolated logic
- **Feature Tests**: `tests/Feature/` - Test HTTP endpoints and workflows
- Use `$this->get()`, `$this->post()` in Feature tests to make HTTP requests
- Tests inherit from `Tests\TestCase` which provides Laravel testing utilities
- Database auto-resets between tests (in-memory SQLite in test mode)

## Important Notes
- Laravel version is 12.0+, using modern PHP 8.2+ features

## When changing database schema or data model 📝
1. Create a migration (`php artisan make:migration`) — do not modify past migrations.
2. Add/update the matching Factory in `database/factories/`.
3. Update or add idempotent seeders in `database/seeders/`.
4. Add tests in `tests/Feature/` or `tests/Unit/` to exercise the change and run `composer run test`.

## Notes for AI agents (explicit, actionable) 💡
- Read `composer.json`, `phpunit.xml`, and `.env.example` first to understand how the app runs and how tests isolate state.
- Default dev/test DB is sqlite; do not assume MySQL is active unless `.env` overrides it.
- Background/visibility tools used in dev: `queue:listen --tries=1` and `pail` for logs—preserve their behavior when adding features that touch queues or logs.
- Keep changes small, covered by tests, and run `vendor/bin/pint` before submitting changes.
- **No Docker setup**. Default dev/test DB is `sqlite` (see `.env.example` and `phpunit.xml`); production may use MySQL via env vars.
- Vite hot reload requires `--host` flag if accessing from different machine
- Artisan is PHP's command runner: all Artisan commands via `php artisan <command>`

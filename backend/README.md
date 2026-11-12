# Service Requests Manager API

This directory contains the Laravel API for Service Requests Manager.

## Requirements

- PHP 8.5 or newer
- Composer 2
- PostgreSQL 14 or newer

For Windows development, use Laragon with PHP 8.5 or newer. If `php` or
`composer` is not recognized in the VS Code terminal, run this once per
PowerShell session:

```powershell
$phpDir = "C:\laragon\bin\php\php-8.5.12-nts-Win32-vs17-x64"
$env:Path = "$phpDir;C:\laragon\bin\composer;$env:Path"
php -v
composer -V
```

Enable these PHP extensions: `pdo_pgsql`, `mbstring`, `xml`, `curl`, `zip`, and
`bcmath`. Start PostgreSQL through Laragon if it is installed there, or through
the local PostgreSQL service.

## Installation

From the repository root:

```powershell
cd backend
$phpDir = "C:\laragon\bin\php\php-8.5.12-nts-Win32-vs17-x64"
$env:Path = "$phpDir;C:\laragon\bin\composer;$env:Path"
composer install
Copy-Item .env.example .env
php artisan key:generate
```

The default `.env.example` uses the following local PostgreSQL connection:

| Setting  | Value                                          |
| -------- | ---------------------------------------------- |
| Host     | `127.0.0.1`                                    |
| Port     | `5432`                                         |
| Database | `service_requests`                             |
| Username | `postgres`                                     |
| Password | Your local PostgreSQL `postgres` user password |

Create a database named `service_requests` in pgAdmin before migrating if it does not
already exist. PostgreSQL must accept password authentication for the
`postgres` user.

```sql
CREATE DATABASE service_requests;
```

If you do not know the password, run this in the pgAdmin Query Tool while
connected as an administrator, replacing the placeholder with a local
development password:

```sql
ALTER USER postgres WITH PASSWORD 'your_postgres_password';
```

Run the migrations and seed the reviewer accounts and sample requests:

```powershell
php artisan migrate:fresh --seed
```

## Running locally

Start the API on port 5000:

```powershell
php artisan serve --port=5000
```

Start the queue worker in a second terminal so queued notifications are processed:

```powershell
php artisan queue:work
```

Keep the worker running during local development. Notifications are stored only
after the queued job is processed.

- API base URL: <http://localhost:5000/api>
- Health check: <http://localhost:5000/up>
- Documentation URL: <http://localhost:5000/docs>
- OpenAPI specification: <http://localhost:5000/openapi.yaml>

## Quality checks

Format PHP files with Laravel Pint:

```powershell
.\vendor\bin\pint
```

Run the Laravel test suite after tests are added:

```powershell
php artisan test
```

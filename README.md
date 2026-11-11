# Service Requests Manager

Service Requests Manager is a service-request and approval application. The
Laravel API is in `backend/`; the frontend will be added under `frontend/`.

## Requirements

- PHP 8.5 or newer
- Composer 2
- PostgreSQL 14 or newer
- Node.js 20 or newer and npm

On Windows, use Laragon for the local PHP environment. Select PHP 8.5 or newer
in Laragon and open a new terminal. If the commands are not found in the VS
Code terminal, add Laragon's PHP 8.5 installation and Composer to the current
PowerShell session:

```powershell
$phpDir = "C:\laragon\bin\php\php-8.5.12-nts-Win32-vs17-x64"
$env:Path = "$phpDir;C:\laragon\bin\composer;$env:Path"
php -v
composer -V
```

Enable the `pdo_pgsql`, `mbstring`, `xml`, `curl`, `zip`, and `bcmath` PHP
extensions in Laragon. Start PostgreSQL through Laragon if it is installed
there, or through the local PostgreSQL service.

## Backend setup

1. Create the local PostgreSQL user and databases:

   Create a database named `quantix` in pgAdmin, using your local PostgreSQL
   administrator account. Use the password configured for the `postgres`
   database user.

2. Install backend dependencies and configure the environment:

   ```powershell
   cd backend
   $phpDir = "C:\laragon\bin\php\php-8.5.12-nts-Win32-vs17-x64"
   $env:Path = "$phpDir;C:\laragon\bin\composer;$env:Path"
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

3. Run database migrations:

   ```powershell
   php artisan migrate
   ```

4. Start the API:

   ```powershell
   php artisan serve --port=5000
   ```

The API is available at <http://localhost:5000>. The health endpoint is
<http://localhost:5000/up>.

See [`backend/README.md`](backend/README.md) for backend-specific commands and
configuration.

## Frontend

The frontend directory is reserved for the client application and is not
initialized yet.

## Development rules

- Do not commit `.env` files, dependencies, logs, or generated build output.
- Run Laravel Pint before submitting backend changes:

  ```powershell
  cd backend
  .\vendor\bin\pint
  ```

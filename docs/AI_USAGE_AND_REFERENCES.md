# AI Usage and References

## 1. AI usage disclosure

This project was developed with AI assistance through GitHub Copilot SDK in
VS Code. AI assistance was used for repository exploration, implementation
drafting, test-case generation, documentation drafting, and reviewing the
implementation against the assignment requirements.

The final design decisions, code changes, test execution, environment
configuration, and requirement verification were reviewed in the repository
working environment. AI assistance did not replace manual verification: the
backend test suite, static analysis, frontend checks, database migrations, and
seed process were executed against this project.

## 2. Main references

### Assignment

The requirements were taken from the **Laravel Internship Take-Home Assignment**
supplied with this project.

### Framework and platform documentation

- [Laravel documentation](https://laravel.com/docs)
- [Laravel 12 documentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum documentation](https://laravel.com/docs/12.x/sanctum)
- [Laravel validation documentation](https://laravel.com/docs/12.x/validation)
- [Laravel authorization documentation](https://laravel.com/docs/12.x/authorization)
- [Laravel queues documentation](https://laravel.com/docs/12.x/queues)
- [Laravel notifications documentation](https://laravel.com/docs/12.x/notifications)
- [Laravel API resources documentation](https://laravel.com/docs/12.x/eloquent-resources)
- [Laravel database testing documentation](https://laravel.com/docs/12.x/database-testing)
- [PHP 8.5 migration guide](https://www.php.net/migration85)
- [Composer documentation](https://getcomposer.org/doc/)
- [PostgreSQL documentation](https://www.postgresql.org/docs/)

### Frontend and testing documentation

- [Next.js documentation](https://nextjs.org/docs)
- [React documentation](https://react.dev/)
- [TanStack Query documentation](https://tanstack.com/query/latest)
- [TypeScript documentation](https://www.typescriptlang.org/docs/)
- [Vitest documentation](https://vitest.dev/guide/)
- [Testing Library documentation](https://testing-library.com/docs/)

## 3. Packages used

### Backend

- Laravel Framework
- Laravel Sanctum
- Pest and Pest Laravel plugin
- Laravel Pint
- Larastan
- FakerPHP
- Symfony YAML

The authoritative versions are recorded in
[`backend/composer.json`](../backend/composer.json) and
[`backend/composer.lock`](../backend/composer.lock).

### Frontend

- Next.js
- React
- TanStack Query
- Tailwind CSS
- Vitest
- React Testing Library

The authoritative versions are recorded in
[`frontend/package.json`](../frontend/package.json) and
[`frontend/package-lock.json`](../frontend/package-lock.json).

## 4. Project documentation map

- [Root setup and workflow README](../README.md)
- [Backend setup README](../backend/README.md)
- [Architecture note](./ARCHITECTURE.md)
- [OpenAPI specification](../backend/docs/openapi.yaml)
- [Postman collection](../backend/docs/postman/ServiceRequests.postman_collection.json)

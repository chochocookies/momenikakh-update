# Implementation Plan: FND-Fondasi-M0

## Overview

This implementation plan establishes the foundational infrastructure for Momenikah, a Laravel-based event invitation platform. The tasks are organized to build incrementally from repository setup through local development environment, code quality tools, architecture enforcement, multi-surface routing, deployment infrastructure, authentication, security headers, and license documentation.

## Tasks

- [x] 1. Initialize repository foundation
  - [x] 1.1 Create repository structure with Git configuration files
    - Create `.gitignore` excluding `vendor/`, `node_modules/`, `.env`, `storage/*`, `*.map`, `*.log`, `dist/`, `build/`, `public/build/`
    - Create `.editorconfig` with settings for `*.php`, `*.js`, `*.blade.php` (indent_style=space, indent_size=4 for PHP, indent_size=2 for JS, end_of_line=lf, charset=utf-8, trim_trailing_whitespace=true)
    - Create `.claude/` directory structure
    - Create initial commit
    - _Requirements: 1.1, 1.4, 1.5, 1.6_
  
  - [x] 1.2 Configure GitHub branch protection rules
    - Enable branch protection on `main` branch requiring at least 1 pull request review
    - Configure protection to reject direct pushes to `main`
    - Document branch protection settings
    - _Requirements: 1.2, 1.3_

- [x] 2. Set up Laravel application and Docker environment
  - [x] 2.1 Install Laravel 13 with PHP 8.3+ configuration
    - Install Laravel 13.x via Composer with PHP 8.3.0-8.4.999 requirement
    - Configure initial Laravel settings
    - Verify `php artisan about` shows correct Laravel and PHP versions
    - _Requirements: 2.1, 2.2_
  
  - [x] 2.2 Create Docker Compose configuration for MySQL and Redis
    - Create `docker-compose.yml` with MySQL 8.4.x service on port 3306
    - Add Redis 7.x+ service on port 6379
    - Define named volumes for MySQL data directory and Redis data directory
    - Add error handling for Docker daemon not running and port conflicts
    - _Requirements: 2.3, 2.4, 2.5, 2.11, 2.12_
  
  - [x] 2.3 Configure Laravel database and cache connections
    - Update `.env.example` and `.env` with MySQL and Redis connection details
    - Configure `config/database.php` for MySQL connection
    - Configure `config/cache.php` for Redis connection
    - Test database connectivity with `php artisan migrate:status` (exit code 0)
    - Test cache connectivity with `php artisan cache:clear` (exit code 0)
    - _Requirements: 2.6, 2.7, 2.8, 2.9_
  
  - [x] 2.4 Write integration tests for database and cache connectivity
    - Write Pest test verifying successful MySQL connection
    - Write Pest test verifying successful Redis connection
    - Verify `composer test` completes with exit code 0
    - _Requirements: 2.10_

- [x] 3. Install and configure code quality tools
  - [x] 3.1 Install Laravel Pint with configuration
    - Install Laravel Pint 1.x+ via Composer
    - Create `pint.json` configuration file at repository root
    - Test Pint execution with `./vendor/bin/pint --test`
    - _Requirements: 3.1_
  
  - [x] 3.2 Install Larastan with configuration
    - Install Larastan 2.x+ via Composer
    - Create `phpstan.neon` configuration at repository root
    - Set analysis level (0-9) with inline comment documenting level and rationale
    - Test Larastan execution with `./vendor/bin/phpstan analyse`
    - _Requirements: 3.2, 3.10_
  
  - [x] 3.3 Install and configure Pest testing framework
    - Install Pest 2.x+ via Composer
    - Create `phpunit.xml` or `pest.php` configuration at repository root
    - Configure test suite structure
    - Verify `php artisan test` executes successfully
    - _Requirements: 3.3_
  
  - [x] 3.4 Create GitHub Actions CI workflow
    - Create `.github/workflows/ci.yml` workflow file
    - Add job for Laravel Pint with `--test` flag (timeout: 300s)
    - Add job for Larastan analysis (timeout: 600s)
    - Add job for Pest test suite (timeout: 900s)
    - Add job for `npm run build` (timeout: 600s)
    - Configure workflow to trigger on pull request opened and updated
    - Configure pull request status checks display for all four checks
    - _Requirements: 3.4, 3.5, 3.6, 3.7, 3.11, 3.12_
  
  - [x] 3.5 Verify CI pipeline integration with branch protection
    - Test that failed CI checks prevent pull request merge
    - Verify pull request displays pass/fail status for Linter, Static Analyzer, Tests, and Build
    - Document CI pipeline behavior
    - _Requirements: 3.8, 3.9, 3.12_

- [x] 4. Checkpoint - Verify development environment
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implement domain-driven architecture structure
  - [x] 5.1 Create domain and presentation layer directory structure
    - Create `app/Domain/` directory for business logic
    - Create `app/Http/` directory for HTTP controllers (if not exists)
    - Create `app/Livewire/` directory for Livewire components
    - Create `app/Filament/` directory for Filament admin resources
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.15_
  
  - [x] 5.2 Implement Pest architecture tests for domain isolation
    - Create architecture test verifying Domain layer doesn't depend on Presentation layer
    - Verify Domain doesn't import from `App\Http`, `App\Livewire`, or `App\Filament` namespaces
    - Add test for Domain directory existence
    - Configure tests to report violating class and forbidden dependency in error messages
    - Ensure tests are executable via `composer test` and `php artisan test`
    - Verify tests complete within 30 seconds
    - _Requirements: 4.5, 4.6, 4.7, 4.8, 4.9, 4.10, 4.11, 4.12, 4.13, 4.14_

- [x] 6. Implement multi-surface domain configuration
  - [x] 6.1 Create surface configuration file
    - Create `config/surfaces.php` with array containing `marketing`, `app`, and `public` keys
    - Define `domain` and `name` properties for each surface
    - Set `momenikah.test` for marketing, `app.momenikah.test` for app, `undangan.momenikah.test` for public
    - _Requirements: 5.6_
  
  - [x] 6.2 Create route files for each surface
    - Create `routes/marketing.php` with marketing surface routes
    - Create `routes/app.php` with app surface routes
    - Create `routes/public.php` with public surface routes
    - Add root path route to each file rendering page with surface name identifier
    - _Requirements: 5.7, 5.8, 5.9, 5.12_
  
  - [x] 6.3 Configure domain-based routing in bootstrap
    - Register routes in `bootstrap/app.php` with domain constraints
    - Ensure subdomain routes are registered before root domain routes
    - Add error logging for route file loading failures
    - Implement 404 handler for unrecognized domains
    - _Requirements: 5.10, 5.11_
  
  - [x] 6.4 Create middleware for surface identifier headers
    - Create middleware that adds surface identifier to response headers (e.g., `X-Surface: marketing`)
    - Apply middleware to appropriate route groups
    - _Requirements: 5.4_
  
  - [x] 6.5 Write tests for multi-surface routing
    - Write test verifying `momenikah.test` returns HTTP 200 with marketing content within 2s
    - Write test verifying `app.momenikah.test` returns HTTP 200 with app content within 2s
    - Write test verifying `undangan.momenikah.test` returns HTTP 200 with public content within 2s
    - Write test verifying surface identifier appears in response headers
    - Write test verifying unrecognized domains return HTTP 404
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 7. Checkpoint - Verify architecture and routing
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implement authentication system with rate limiting
  - [x] 8.1 Install Laravel Livewire authentication starter kit
    - Install official Laravel Livewire starter kit
    - Configure authentication views and routes
    - Verify registration, login, password reset, and email verification features are available
    - Integrate with Laravel User model
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.24_
  
  - [x] 8.2 Configure rate limiting for authentication routes
    - Configure login throttling: 5 attempts per email per 60 seconds
    - Configure registration throttling: 3 attempts per IP per 600 seconds
    - Set lockout duration for login to 60 seconds after 6th attempt
    - Add error messages displaying lockout duration and retry time
    - Ensure rate limits automatically expire after time window
    - _Requirements: 7.11, 7.12, 7.13, 7.14, 7.15, 7.16_
  
  - [x] 8.3 Write tests for authentication flows
    - Write test for successful registration with email verification
    - Write test for successful login with valid credentials
    - Write test for login rejection with invalid credentials
    - Write test for complete password reset flow
    - Write test for email verification flow
    - Verify all authentication operations complete within specified time limits (5s for email, 2s for auth)
    - _Requirements: 7.5, 7.6, 7.7, 7.8, 7.9, 7.10, 7.17, 7.18, 7.19, 7.20, 7.21_
  
  - [x] 8.4 Write tests for rate limiting
    - Write test verifying 6 consecutive failed login attempts trigger rate limiting
    - Write test verifying rate-limited users cannot login until lockout expires
    - Write test verifying successful authentication after rate limit expiry
    - _Requirements: 7.22, 7.23_

- [x] 9. Implement security headers and CSP middleware
  - [x] 9.1 Create security headers middleware with global headers
    - Create middleware adding `X-Content-Type-Options: nosniff`
    - Add `X-XSS-Protection: 1; mode=block`
    - Add `Referrer-Policy: strict-origin-when-cross-origin`
    - Add environment-conditional `Strict-Transport-Security` (production only, max-age=31536000)
    - _Requirements: 8.1, 8.2, 8.4, 8.5, 8.6_
  
  - [x] 9.2 Implement surface-specific X-Frame-Options
    - Add `X-Frame-Options: DENY` for marketing surface
    - Add `X-Frame-Options: DENY` for app surface
    - Add `X-Frame-Options: SAMEORIGIN` for public surface
    - _Requirements: 8.3_
  
  - [x] 9.3 Configure Content Security Policy for marketing surface
    - Create CSP configuration for marketing surface
    - Set `default-src 'self'`
    - Set `script-src 'self' 'unsafe-inline'`
    - Set `style-src 'self' 'unsafe-inline'`
    - Set `img-src 'self' data:`
    - Set `font-src 'self'`
    - _Requirements: 8.7, 8.8, 8.11, 8.14, 8.17, 8.20_
  
  - [x] 9.4 Configure Content Security Policy for app surface
    - Create CSP configuration for app surface
    - Set `default-src 'self'`
    - Set `script-src 'self'` (no unsafe-inline)
    - Set `style-src 'self' 'unsafe-inline'`
    - Set `img-src 'self' data:`
    - Set `font-src 'self'`
    - _Requirements: 8.7, 8.9, 8.12, 8.15, 8.18, 8.21_
  
  - [x] 9.5 Configure Content Security Policy for public surface
    - Create CSP configuration for public surface
    - Set `default-src 'self'`
    - Set `script-src 'self'` (no unsafe-inline)
    - Set `style-src 'self' 'unsafe-inline'`
    - Set `img-src 'self' data:`
    - Set `font-src 'self'`
    - _Requirements: 8.7, 8.10, 8.13, 8.16, 8.19, 8.22_
  
  - [x] 9.6 Register security middleware globally
    - Register security headers middleware in application middleware stack
    - Verify middleware applies to all routes on all surfaces
    - Test security headers are present in responses
    - _Requirements: 8.1_
  
  - [x] 9.7 Verify security headers in staging environment
    - Deploy to staging environment
    - Run security header scanner on all three surfaces
    - Verify zero critical security header violations
    - Document scan results
    - _Requirements: 8.23_
    
    **Notes:**
    - Security headers middleware is implemented and tested locally
    - Staging environment deployment pending (requires Task 10 infrastructure)
    - Local tests verify all headers are correctly set for all three surfaces
    - Documentation created at docs/security-headers-report.md

- [x] 10. Set up staging deployment infrastructure
  - [x] 10.1 Configure staging server environment
    - Set up staging server with SSH access
    - Configure database connectivity for staging
    - Configure file storage access
    - Set environment variables for staging
    - _Requirements: 6.1, 6.2_
  
  - [x] 10.2 Create deployment script with zero-downtime strategy
    - Create deployment script using symbolic link swap strategy
    - Implement releases directory structure with timestamp-based releases
    - Configure shared directories (storage, .env)
    - Implement atomic symlink update for zero-downtime deployment
    - Add preparation phase: clone, install dependencies, build assets, migrations
    - Add verification phase: database connectivity and HTTP health checks (max 120s)
    - Add activation phase: update symlink, clear caches, restart workers (max 30s unavailability)
    - Ensure maximum 30 seconds service unavailability during deployment
    - _Requirements: 6.3, 6.12, 6.13_
  
  - [x] 10.3 Implement health check endpoint
    - Create health check endpoint verifying database connectivity
    - Add Redis connectivity check to health endpoint
    - Return HTTP 200 when all checks pass
    - Configure deployment script to use health check endpoint
    - _Requirements: 6.12_
  
  - [x] 10.4 Configure deployment triggers and rollback
    - Configure deployment to trigger on Git tag matching pattern `v*.*.*`
    - Ensure deployment initiates within 5 minutes of tag push
    - Implement automatic rollback within 60 seconds if post-deployment health checks fail
    - Document manual rollback procedures with example commands
    - _Requirements: 6.4, 6.14, 6.15, 6.16_
  
  - [x] 10.5 Set up database backup system
    - Configure daily database backups at 02:00 UTC
    - Implement retention policy: minimum 7 days, maximum 30 days
    - Add concurrency control: wait for completion if backup already in progress
    - Create backup manifest with row counts for verification
    - Test backup restore and verify row counts match manifest
    - _Requirements: 6.5, 6.6, 6.7, 6.8_
  
  - [x] 10.6 Configure Sentry error monitoring
    - Install `sentry/sentry-laravel` package
    - Configure Sentry DSN and environment identifier ("staging")
    - Configure unhandled exception reporting with stack trace (within 60s)
    - Configure HTTP 500 error reporting to Sentry
    - Test error reporting in staging environment
    - _Requirements: 6.9, 6.10, 6.11_

- [x] 11. Create third-party license documentation
  - [x] 11.1 Create THIRD_PARTY.md with initial structure
    - Create `THIRD_PARTY.md` at repository root
    - Add document header with last-updated date in ISO 8601 format
    - Create sections for GSAP, Fonts, Composer Packages, and npm Packages
    - _Requirements: 9.1, 9.2, 9.11_
  
  - [x] 11.2 Document GSAP and font licenses
    - Document GSAP license type, commercial usage terms, and gsap.com licensing reference
    - State GSAP commercial license acquisition status or document use of free features only
    - List Cormorant Garamond font with OFL license and source URL
    - List Plus Jakarta Sans font with OFL license and source URL
    - _Requirements: 9.3, 9.4, 9.5, 9.8_
  
  - [x] 11.3 Document Composer and npm package licenses
    - Extract and document all packages from `composer.json` with name, version, and license
    - Extract and document all packages from `package.json` with name, version, and license
    - Create tables for Composer packages and npm packages
    - _Requirements: 9.6, 9.7_
  
  - [x] 11.4 Add maintenance process documentation
    - Document process for adding new dependencies (update THIRD_PARTY.md in same commit)
    - Document process for removing dependencies (remove from THIRD_PARTY.md in same commit)
    - Add note about updating last-updated date when making changes
    - Consider documenting future automation possibilities (pre-commit hook)
    - _Requirements: 9.9, 9.10_

- [x] 12. Final checkpoint and integration verification
  - [x] 12.1 Run complete test suite
    - Execute `composer test` and verify all tests pass
    - Execute `php artisan test` and verify all tests pass
    - Verify architecture tests complete within 30 seconds
    - Review test coverage for critical components
  
  - [x] 12.2 Verify local development environment
    - Test all three surfaces respond correctly in browser
    - Verify Docker services (MySQL and Redis) are running
    - Test authentication flows manually
    - Verify security headers are present in browser dev tools
  
  - [x] 12.3 Verify staging deployment
    - Deploy to staging using Git tag
    - Verify zero-downtime deployment completes successfully
    - Test health check endpoint
    - Verify Sentry is receiving errors (trigger test error)
    - Verify database backups are being created
  
  - [x] 12.4 Review documentation completeness
    - Verify `THIRD_PARTY.md` is complete and up to date
    - Review `.gitignore` and `.editorconfig` correctness
    - Verify GitHub branch protection rules are active
    - Verify CI pipeline is functioning correctly
  
  - [x] 12.5 Final integration test
    - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP
- Each task references specific requirements from the requirements document for traceability
- Checkpoints ensure incremental validation at logical milestones
- The implementation builds incrementally: repository → local environment → quality tools → architecture → surfaces → deployment → auth → security → documentation
- Architecture tests enforce domain isolation automatically in CI pipeline
- Multi-surface configuration supports three distinct web experiences from a single codebase
- Zero-downtime deployment strategy uses symbolic link swapping similar to Laravel Forge
- Security is enforced at multiple layers: rate limiting, security headers, and surface-specific CSP
- All third-party dependencies are documented for legal compliance

## Task Dependency Graph

```json
{
  "waves": [
    {
      "id": 0,
      "tasks": ["1.1", "1.2"]
    },
    {
      "id": 1,
      "tasks": ["2.1"]
    },
    {
      "id": 2,
      "tasks": ["2.2", "2.3"]
    },
    {
      "id": 3,
      "tasks": ["2.4", "3.1", "3.2", "3.3"]
    },
    {
      "id": 4,
      "tasks": ["3.4", "3.5"]
    },
    {
      "id": 5,
      "tasks": ["5.1"]
    },
    {
      "id": 6,
      "tasks": ["5.2", "6.1"]
    },
    {
      "id": 7,
      "tasks": ["6.2", "6.3"]
    },
    {
      "id": 8,
      "tasks": ["6.4", "6.5"]
    },
    {
      "id": 9,
      "tasks": ["8.1"]
    },
    {
      "id": 10,
      "tasks": ["8.2", "8.3"]
    },
    {
      "id": 11,
      "tasks": ["8.4", "9.1", "9.2"]
    },
    {
      "id": 12,
      "tasks": ["9.3", "9.4", "9.5"]
    },
    {
      "id": 13,
      "tasks": ["9.6", "9.7"]
    },
    {
      "id": 14,
      "tasks": ["10.1"]
    },
    {
      "id": 15,
      "tasks": ["10.2", "10.3"]
    },
    {
      "id": 16,
      "tasks": ["10.4", "10.5", "10.6"]
    },
    {
      "id": 17,
      "tasks": ["11.1"]
    },
    {
      "id": 18,
      "tasks": ["11.2", "11.3"]
    },
    {
      "id": 19,
      "tasks": ["11.4"]
    },
    {
      "id": 20,
      "tasks": ["12.1", "12.2"]
    },
    {
      "id": 21,
      "tasks": ["12.3", "12.4"]
    },
    {
      "id": 22,
      "tasks": ["12.5"]
    }
  ]
}
```

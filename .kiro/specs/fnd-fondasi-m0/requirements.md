# Requirements Document

## Introduction

The FND-Fondasi-M0 milestone establishes the foundational infrastructure for Momenikah, a Laravel-based event invitation platform. This milestone delivers the core development environment, repository structure, deployment pipeline, authentication system, and security foundation required for all subsequent development work. The system will support three distinct web surfaces (marketing, application, and public invitations) on a modular monolithic architecture with strict domain isolation enforced through automated testing.

## Glossary

- **Repository**: The Git repository containing the Momenikah codebase
- **Laravel_Application**: The Laravel 13 framework instance running the Momenikah platform
- **Docker_Environment**: The containerized MySQL and Redis services for local development
- **CI_Pipeline**: The GitHub Actions continuous integration pipeline
- **Linter**: Laravel Pint code style enforcement tool
- **Static_Analyzer**: Larastan static analysis tool for PHP code
- **Test_Suite**: Pest testing framework and test collection
- **Domain_Layer**: The `app/Domain/*` business logic layer
- **Presentation_Layer**: The `app/Http`, `app/Livewire`, and `app/Filament` layers
- **Architecture_Test**: Pest architectural tests that enforce dependency rules
- **Surface**: One of three distinct web applications served by the platform (marketing, app, or public)
- **Marketing_Surface**: The main website at `momenikah.test` for landing pages and marketing content
- **App_Surface**: The authenticated application at `app.momenikah.test` for user dashboards and admin
- **Public_Surface**: The public invitation viewer at `undangan.momenikah.test` without sessions
- **Surface_Configuration**: The `config/surfaces.php` configuration defining surface-specific settings
- **Route_File**: One of `routes/marketing.php`, `routes/app.php`, or `routes/public.php`
- **Staging_Environment**: The pre-production deployment environment for testing
- **Deploy_Script**: The automated deployment script with zero-downtime capability
- **Backup_System**: The automated daily database backup system
- **Sentry**: The error tracking and monitoring service
- **Auth_System**: The Laravel Livewire authentication starter kit implementation
- **Throttle**: Rate limiting mechanism to prevent abuse
- **Security_Headers**: HTTP security headers middleware
- **CSP**: Content Security Policy headers specific to each surface
- **Third_Party_Documentation**: The `THIRD_PARTY.md` file documenting external dependencies and licenses

## Requirements

### Requirement 1: Repository Foundation

**User Story:** As a developer, I want a properly configured Git repository with protection and coding standards, so that the codebase maintains quality and consistency.

#### Acceptance Criteria

1. THE Repository SHALL exist with initial commit containing `.gitignore`, `.editorconfig`, and `.claude/` directory
2. THE Repository SHALL have branch protection enabled on `main` requiring at least 1 pull request review before merge
3. THE Repository SHALL reject direct pushes to `main` branch
4. THE `.gitignore` file SHALL exclude `vendor/`, `node_modules/`, `.env`, and all subdirectories within `storage/`
5. THE `.gitignore` file SHALL exclude `*.map`, `*.log`, `dist/`, `build/`, and `public/build/`
6. THE `.editorconfig` file SHALL define `indent_style`, `indent_size`, `end_of_line`, `charset`, and `trim_trailing_whitespace` settings for `*.php`, `*.js`, and `*.blade.php` file patterns

### Requirement 2: Laravel Installation and Local Environment

**User Story:** As a developer, I want Laravel 13 with MySQL and Redis running locally via Docker, so that I have a consistent development environment.

#### Acceptance Criteria

1. THE Laravel_Application SHALL be Laravel version 13.0.0 to 13.999.999 with PHP version 8.3.0 to 8.4.999
2. WHEN `php artisan about` is executed, THE command SHALL display Laravel version matching installed version, PHP version matching runtime version, and environment set to "local"
3. THE Docker_Environment SHALL provide MySQL service version 8.4.x listening on port 3306
4. THE Docker_Environment SHALL provide Redis service version 7.x or higher listening on port 6379
5. THE Docker_Environment SHALL define named volumes for MySQL data directory and Redis data directory in `docker-compose.yml`
6. WHEN Laravel_Application attempts database connection, THE MySQL_Service SHALL accept connections using configured credentials within 5 seconds
7. WHEN Laravel_Application attempts cache connection, THE Redis_Service SHALL accept connections using configured credentials within 5 seconds
8. WHEN `php artisan migrate:status` is executed, THE command SHALL complete with exit code 0 indicating successful database connection
9. WHEN `php artisan cache:clear` is executed, THE command SHALL complete with exit code 0 indicating successful Redis connection
10. WHEN `composer test` is executed, THE Test_Suite SHALL complete with exit code 0
11. IF Docker daemon is not running, THEN THE Docker_Environment commands SHALL return error indicating Docker is unavailable
12. IF MySQL or Redis ports are already in use, THEN THE Docker_Environment startup SHALL fail with error message indicating port conflict

### Requirement 3: Code Quality Tools and CI Pipeline

**User Story:** As a developer, I want automated code quality checks in CI, so that all code meets quality standards before merging.

#### Acceptance Criteria

1. THE Repository SHALL have Laravel Pint version 1.x or later installed via Composer and configured with a `pint.json` configuration file at repository root
2. THE Repository SHALL have Larastan version 2.x or later installed via Composer with configuration defined in `phpstan.neon` or `phpstan.neon.dist` at repository root
3. THE Repository SHALL have Pest version 2.x or later installed via Composer with configuration defined in `phpunit.xml` or `pest.php` at repository root
4. WHEN a pull request is opened or updated, THE CI_Pipeline SHALL execute Laravel Pint with `--test` flag and complete within 300 seconds
5. WHEN a pull request is opened or updated, THE CI_Pipeline SHALL execute Larastan analysis and complete within 600 seconds
6. WHEN a pull request is opened or updated, THE CI_Pipeline SHALL execute Pest test suite and complete within 900 seconds
7. WHEN a pull request is opened or updated, THE CI_Pipeline SHALL execute `npm run build` for frontend assets and complete within 600 seconds
8. IF any CI_Pipeline check exits with non-zero status code, THEN THE pull request status SHALL display as failed
9. IF pull request status displays as failed, THEN THE repository branch protection SHALL prevent merge until all checks pass
10. THE Larastan configuration SHALL specify analysis level as integer between 0 and 9 with inline comment documenting current level and rationale
11. THE CI_Pipeline SHALL be defined as GitHub Actions workflow file in `.github/workflows/` directory with `.yml` or `.yaml` extension
12. WHEN CI_Pipeline completes, THE pull request SHALL display check results showing pass/fail status for Linter, Static_Analyzer, Test_Suite, and build verification

### Requirement 4: Domain-Driven Architecture Structure

**User Story:** As a developer, I want the domain layer isolated from presentation layers, so that business logic remains independent and testable.

#### Acceptance Criteria

1. THE Laravel_Application SHALL have directory structure `app/Domain/` for business logic
2. THE Laravel_Application SHALL have directory structure `app/Http/` for HTTP controllers
3. THE Laravel_Application SHALL have directory structure `app/Livewire/` for Livewire components
4. THE Laravel_Application SHALL have directory structure `app/Filament/` for Filament admin resources
5. THE Architecture_Test SHALL verify that Domain_Layer classes do not depend on Presentation_Layer classes
6. THE Architecture_Test SHALL verify that Domain_Layer does not import from `App\Http` namespace
7. THE Architecture_Test SHALL verify that Domain_Layer does not import from `App\Livewire` namespace
8. THE Architecture_Test SHALL verify that Domain_Layer does not import from `App\Filament` namespace
9. IF Architecture_Test detects dependency violation, THEN THE Test_Suite SHALL fail with error message indicating the violating class and forbidden dependency
10. THE Architecture_Test SHALL be executable via `composer test` command
11. THE Architecture_Test SHALL be executable via `php artisan test` command
12. WHEN developer runs architecture tests, THE Test_Suite SHALL complete execution within 30 seconds
13. IF Domain_Layer class imports namespace matching pattern `App\Http\*`, `App\Livewire\*`, or `App\Filament\*`, THEN THE Architecture_Test SHALL report this as dependency violation
14. THE Architecture_Test SHALL scan all PHP files within `app/Domain/` directory recursively
15. IF `app/Domain/` directory does not exist, THEN THE Architecture_Test SHALL report failure indicating missing Domain_Layer directory

### Requirement 5: Multi-Surface Domain Configuration

**User Story:** As a developer, I want three separate local domains for different surfaces, so that I can develop and test surface-specific features in isolation.

#### Acceptance Criteria

1. WHEN THE Laravel_Application receives HTTP request on `momenikah.test` domain, THE Laravel_Application SHALL return HTTP 200 status response with content from Marketing_Surface within 2 seconds
2. WHEN THE Laravel_Application receives HTTP request on `app.momenikah.test` domain, THE Laravel_Application SHALL return HTTP 200 status response with content from App_Surface within 2 seconds
3. WHEN THE Laravel_Application receives HTTP request on `undangan.momenikah.test` domain, THE Laravel_Application SHALL return HTTP 200 status response with content from Public_Surface within 2 seconds
4. WHEN THE Laravel_Application receives HTTP request on any of the three configured domains, THE Laravel_Application SHALL include surface identifier in response headers indicating which surface served the request
5. IF THE Laravel_Application receives HTTP request on domain not matching `momenikah.test`, `app.momenikah.test`, or `undangan.momenikah.test`, THEN THE Laravel_Application SHALL return HTTP 404 status response
6. THE Surface_Configuration file SHALL exist at `config/surfaces.php` containing array with keys `marketing`, `app`, and `public`, each defining `domain` and `name` properties
7. THE Route_File `routes/marketing.php` SHALL exist and contain route definitions for Marketing_Surface
8. THE Route_File `routes/app.php` SHALL exist and contain route definitions for App_Surface
9. THE Route_File `routes/public.php` SHALL exist and contain route definitions for Public_Surface
10. THE `bootstrap/app.php` SHALL register all three Route_File configurations before application initialization completes
11. IF any Route_File fails to load during application initialization, THEN THE Laravel_Application SHALL log error message indicating which Route_File failed and halt initialization
12. WHEN accessing root path of each configured domain, THE Laravel_Application SHALL render page containing text identifying the surface name matching that domain

### Requirement 6: Staging Environment and Deployment Infrastructure

**User Story:** As a developer, I want automated staging deployment with backups and monitoring, so that I can safely deploy and recover from issues.

#### Acceptance Criteria

1. THE Staging_Environment SHALL be accessible via HTTPS to deployment systems and development team networks
2. WHEN Laravel_Application is deployed, THE Staging_Environment SHALL have database connectivity, file storage access, and environment variables configured
3. THE Deploy_Script SHALL complete deployment to Staging_Environment with maximum 30 seconds of service unavailability
4. WHEN a Git tag matching pattern "v*.*.*" is pushed to the repository, THE Deploy_Script SHALL initiate deployment within 5 minutes
5. THE Backup_System SHALL create database backups daily at 02:00 UTC
6. IF a backup creation is already in progress, THEN THE Backup_System SHALL wait for completion before starting the next backup
7. THE Backup_System SHALL retain backups for minimum 7 days and maximum 30 days
8. WHEN a backup restore is tested, THE Backup_System SHALL restore database and verify row counts match the backup manifest
9. THE Sentry integration SHALL be configured in Laravel_Application with DSN and environment identifier set to "staging"
10. WHEN an unhandled exception occurs in Staging_Environment, THE Sentry integration SHALL report the error with stack trace within 60 seconds
11. WHEN an HTTP 500 error is returned, THE Sentry integration SHALL report the error to Sentry
12. WHEN Deploy_Script initiates deployment, THE Deploy_Script SHALL verify database connectivity and application HTTP 200 response before marking deployment complete
13. THE Deploy_Script SHALL wait maximum 120 seconds for health checks to pass
14. IF health checks fail, THEN THE Deploy_Script SHALL abort deployment and retain previous version
15. IF deployment fails after traffic switch, THEN THE Deploy_Script SHALL automatically rollback to previous deployment within 60 seconds
16. THE Deploy_Script documentation SHALL include step-by-step rollback procedures with example commands

### Requirement 7: Authentication System with Rate Limiting

**User Story:** As a user, I want to register, login, reset password, and verify email securely, so that my account is protected.

#### Acceptance Criteria

1. THE Auth_System SHALL provide user registration functionality using official Laravel Livewire starter kit
2. THE Auth_System SHALL provide user login functionality using official Laravel Livewire starter kit
3. THE Auth_System SHALL provide password reset functionality using official Laravel Livewire starter kit
4. THE Auth_System SHALL provide email verification functionality using official Laravel Livewire starter kit
5. WHEN a user submits valid registration data, THEN THE Auth_System SHALL create a user account and send a verification email within 5 seconds
6. WHEN a user submits valid login credentials AND email is verified, THEN THE Auth_System SHALL authenticate the user and establish a session within 2 seconds
7. WHEN a user submits invalid login credentials, THEN THE Auth_System SHALL reject authentication and display an error message indicating invalid credentials
8. WHEN a user requests password reset, THEN THE Auth_System SHALL send a password reset link to the registered email within 5 seconds
9. WHEN a user clicks a valid password reset link AND submits a new password meeting requirements, THEN THE Auth_System SHALL update the password and invalidate the reset token
10. WHEN a user clicks a valid email verification link, THEN THE Auth_System SHALL mark the email as verified within 2 seconds
11. THE Throttle SHALL limit login attempts to 5 attempts per email address per 60 seconds
12. WHEN a user exceeds 5 login attempts within 60 seconds, THEN THE Auth_System SHALL block further login attempts for that email address for 60 seconds
13. WHEN a login attempt is blocked by rate limiting, THEN THE Auth_System SHALL display an error message indicating the lockout duration remaining
14. THE Throttle SHALL limit registration attempts to 3 attempts per IP address per 600 seconds
15. WHEN a user exceeds registration attempt limit, THEN THE Auth_System SHALL block further registration attempts from that IP address and display an error message indicating retry time
16. WHEN a rate limit expires, THEN THE Auth_System SHALL allow new authentication attempts immediately
17. THE Test_Suite SHALL include automated tests verifying successful user registration with email verification
18. THE Test_Suite SHALL include automated tests verifying successful login with valid credentials
19. THE Test_Suite SHALL include automated tests verifying login rejection with invalid credentials
20. THE Test_Suite SHALL include automated tests verifying password reset flow from request to completion
21. THE Test_Suite SHALL include automated tests verifying email verification flow
22. THE Test_Suite SHALL include automated tests verifying that 6 consecutive failed login attempts trigger rate limiting
23. THE Test_Suite SHALL include automated tests verifying that rate-limited users cannot login until lockout period expires
24. THE Auth_System SHALL integrate with Laravel_Application user model

### Requirement 8: Security Headers and Content Security Policy

**User Story:** As a security-conscious developer, I want security headers on all responses, so that the application is protected from common web vulnerabilities.

#### Acceptance Criteria

1. THE Laravel_Application SHALL have middleware that adds security headers to all HTTP responses
2. THE Security_Headers SHALL include `X-Content-Type-Options` with value `nosniff`
3. THE Security_Headers SHALL include `X-Frame-Options` with value `DENY` for Marketing_Surface and App_Surface, and value `SAMEORIGIN` for Public_Surface
4. THE Security_Headers SHALL include `X-XSS-Protection` with value `1; mode=block`
5. THE Security_Headers SHALL include `Referrer-Policy` with value `strict-origin-when-cross-origin`
6. IF Environment is Production_Environment, THEN THE Security_Headers SHALL include `Strict-Transport-Security` with minimum max-age of 31536000 seconds
7. THE Laravel_Application SHALL have separate CSP configurations for Marketing_Surface, App_Surface, and Public_Surface
8. THE Marketing_Surface CSP SHALL include `script-src` directive allowing `'self'` and `'unsafe-inline'`
9. THE App_Surface CSP SHALL include `script-src` directive allowing `'self'` only
10. THE Public_Surface CSP SHALL include `script-src` directive allowing `'self'` only
11. THE Marketing_Surface CSP SHALL include `style-src` directive allowing `'self'` and `'unsafe-inline'`
12. THE App_Surface CSP SHALL include `style-src` directive allowing `'self'` and `'unsafe-inline'`
13. THE Public_Surface CSP SHALL include `style-src` directive allowing `'self'` and `'unsafe-inline'`
14. THE Marketing_Surface CSP SHALL include `img-src` directive allowing `'self'` and `data:`
15. THE App_Surface CSP SHALL include `img-src` directive allowing `'self'` and `data:`
16. THE Public_Surface CSP SHALL include `img-src` directive allowing `'self'` and `data:`
17. THE Marketing_Surface CSP SHALL include `font-src` directive allowing `'self'`
18. THE App_Surface CSP SHALL include `font-src` directive allowing `'self'`
19. THE Public_Surface CSP SHALL include `font-src` directive allowing `'self'`
20. THE Marketing_Surface CSP SHALL include `default-src` directive with value `'self'`
21. THE App_Surface CSP SHALL include `default-src` directive with value `'self'`
22. THE Public_Surface CSP SHALL include `default-src` directive with value `'self'`
23. WHEN Staging_Environment is scanned with security header scanner, THEN THE scan SHALL report zero critical security header violations for all three surfaces

### Requirement 9: Third-Party License Documentation

**User Story:** As a project maintainer, I want documented licenses for all third-party dependencies, so that we comply with licensing requirements and know usage rights.

#### Acceptance Criteria

1. THE Third_Party_Documentation SHALL exist at `THIRD_PARTY.md` in repository root
2. THE Third_Party_Documentation SHALL organize dependencies in sections for GSAP, Fonts, Composer Packages, and npm Packages
3. THE Third_Party_Documentation SHALL document GSAP license type, commercial usage terms, and reference URL to gsap.com licensing page
4. THE Third_Party_Documentation SHALL list Cormorant Garamond font with license type OFL and source URL
5. THE Third_Party_Documentation SHALL list Plus Jakarta Sans font with license type OFL and source URL
6. THE Third_Party_Documentation SHALL list each Composer package with package name, version number, and license type at time of initial installation
7. THE Third_Party_Documentation SHALL list each npm package with package name, version number, and license type at time of initial installation
8. WHERE GSAP commercial license is required, THE Third_Party_Documentation SHALL state acquisition status or document use of GSAP free features only
9. WHEN dependency packages are added to composer.json or package.json after FND-03, THE Third_Party_Documentation SHALL be updated to include name, version, and license type of added packages within same commit
10. WHEN dependency packages are removed from composer.json or package.json after FND-03, THE Third_Party_Documentation SHALL be updated to remove corresponding entries within same commit
11. THE Third_Party_Documentation SHALL include last-updated date in ISO 8601 format at document header

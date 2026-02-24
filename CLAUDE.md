# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SaferPay Official is a PrestaShop payment module integrating SIX Payment Services (Worldline). It supports 23+ payment methods (credit cards, digital wallets, bank transfers, Klarna, PayPal, etc.) with features like 3DS, saved card tokenization, and partial refunds.

- **Namespace:** `Invertus\SaferPay`
- **PrestaShop compatibility:** 1.7.6.1+
- **PHP platform target:** 5.6 (via composer config)
- **Current version:** 2.0.2

## Build & Development Commands

```bash
# Install dependencies
composer install

# Run unit tests
composer test-unit
# or: ./vendor/bin/phpunit --configuration tests/Unit/phpunit.xml

# Run integration tests
composer test-integration

# Lint (auto-fix with PHP CS Fixer)
make fix-lint                    # via Docker
./vendor/bin/php-cs-fixer fix    # directly

# Lint dry-run (CI check)
./vendor/bin/php-cs-fixer fix --diff --no-interaction --dry-run

# PHPStan static analysis (needs _PS_ROOT_DIR_ set)
_PS_ROOT_DIR_=/var/www/html ./vendor/bin/phpstan --configuration=tests/phpstan/phpstan.neon analyse

# Build production ZIP
make prepare-zip

# Docker E2E environment (PS 1.7.8.4 example)
make e2e1784p    # start containers + seed DB + install module
make bps1784     # just build/install module in running container
```

Version-specific PHPStan configs exist at `tests/phpstan/phpstan-{version}.neon` (e.g., `phpstan-1784.neon`).

## Code Style

Enforced by `.php_cs.dist` — PSR-2 plus:
- Short array syntax `[]`
- Trailing commas in multiline arrays
- Braces on next line for functions/OOP constructs
- Single space around concatenation (`.`)
- No unused imports, no useless else/return

## Architecture

### Entry Point

`saferpayofficial.php` — Main module class `SaferPayOfficial extends PaymentModule`. Registers hooks, handles install/uninstall, and provides `$this->getService($serviceName)` for DI.

### Dependency Injection

Uses **League Container** with auto-wiring via `ReflectionContainer`. Interface-to-implementation bindings are registered in `src/ServiceProvider/BaseServiceProvider.php`. The container is created fresh per `getService()` call in `LeagueServiceContainerProvider`. Services can be overridden via `extend($id, $concrete)` for testing.

### src/ Layout

| Directory | Purpose |
|-----------|---------|
| `Adapter/` | Wrappers around PrestaShop globals (Context, Configuration, Tools, Cart) |
| `Api/` | HTTP client (`ApiRequest`) and API service classes (Initialize, Assert, Capture, Refund, Cancel) |
| `Config/SaferPayConfig.php` | All constants: API URLs, config keys, payment method definitions, hook list |
| `Controller/` | Abstract base controllers for admin and front |
| `Core/` | Order actions, payment processing, verification logic |
| `DTO/` | Request and Response data transfer objects for the SaferPay API |
| `Entity/` | PrestaShop ObjectModel entities (12 tables: `saferpay_order`, `saferpay_payment`, `saferpay_card_alias`, etc.) |
| `EntityBuilder/` | Construct entities from API responses |
| `EntityManager/` | Persistence abstraction (`ObjectModelEntityManager`) |
| `Exception/` | Domain exceptions with error codes |
| `Factory/` | Object creation |
| `Install/` | `Installer` and `Uninstaller` — DB table creation, hook registration, default config |
| `Logger/` | Logging to `saferpay_log` table with `LogFormatter` |
| `Presenter/` | Prepare data for templates (admin order page, assertions) |
| `Processor/` | Request processing handlers |
| `Provider/` | Payment type info, restrictions, currencies, redirect URLs |
| `Repository/` | ~20 repository classes for data access, with interface abstractions |
| `Service/` | ~27 service classes: payment init, status updates, mail, cart ops, restriction validation, transaction flows |
| `ServiceProvider/` | League Container setup and base service registration |
| `Utility/` | Helper functions |
| `Validation/` | Input validation |

### Controllers

- **Admin** (`controllers/admin/`, 6 controllers): Settings, Payment management, Logs, Order details, Fields, Module redirect
- **Front** (`controllers/front/`, 15 controllers): Payment flow (ajax, validation, iframe, hostedIframe, return, notify), success/fail pages, saved credit cards, pending notifications

### Payment Transaction Flow

1. **Initialize** → `SaferPayInitialize` / `InitializeService` — create payment session, redirect customer
2. **Assert** → `SaferPayTransactionAssertion` / `AssertService` — verify payment after return
3. **Authorize/Capture** → `SaferPayTransactionAuthorization` / `AuthorizationService` / `CaptureService`
4. **Refund** → `SaferPayTransactionRefundAssertion` / `RefundService`
5. **Webhook notifications** → `notify.php` / `pendingNotify.php` controllers

### Database

12 custom tables prefixed with `saferpay_` (order, payment, card_alias, field, logo, restriction, assert, assert_refund, order_refund, log, country, currency). Schema managed in `src/Install/Installer.php`. Version migrations in `upgrade/install-{version}.php`.

### Templates & Assets

Smarty `.tpl` templates in `views/templates/` (admin, front, hook subdirectories). CSS/JS assets in `views/css/` and `views/js/` with admin/front separation.

## Testing

- **Unit tests:** `tests/Unit/` — PHPUnit with `tests/Unit/bootstrap.php`; base class `UnitTestCase`
- **Integration tests:** `tests/Integration/`
- **E2E tests:** `cypress/` — Cypress 9.x, run via BrowserStack in CI across PS versions
- **Test DB seeds:** `tests/seed/database/` — SQL dumps per PrestaShop version

## CI/CD

GitHub Actions in `.github/workflows/`:
- **cd.yml**: Lint + PHPStan matrix across PS versions, then build artifact ZIP
- **Cypress workflows**: Per-version E2E tests on BrowserStack
- PRs should target the latest release branch (e.g., `release-2.0.0`)

## Key Configuration

`src/Config/SaferPayConfig.php` contains:
- API endpoints (test: `test.saferpay.com/api/`, live: `www.saferpay.com/api/`)
- API version constant (`1.45`)
- All `Configuration` key names (`SAFERPAY_TEST_MODE`, `SAFERPAY_USERNAME`, etc.)
- Payment method name constants and supported methods lists
- Hook names the module registers

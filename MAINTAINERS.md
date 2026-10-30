# Maintainers Guide

## Quick Start

### Setup
```bash
cd prestashop/modules
git clone https://github.com/Invertus/saferpayofficial.git saferpayofficial
cd saferpayofficial
composer install
```

## Development

### Code Quality
```bash
make ci-lint      # php-cs-fixer with the PrestaShop coding standard, dry run
make fix-lint     # same rules, applies the fixes
make ci-phpstan   # needs a PrestaShop root at /var/www/html, run it inside a PrestaShop container
```
The style and static analysis tools are installed from `tests/ci-tools/composer.json`, apart from the
module's own dependencies, and need PHP 7.4 or newer.
On every push to the branch, the CI/CD GitHub Actions will be triggered.

## New feature pull request

- All CI/CD GitHub Actions should be passing
- Pointed to latest release branch e.g. `release-2.0.0`
- Description should be clear and concise, explaining the purpose of the pull request.
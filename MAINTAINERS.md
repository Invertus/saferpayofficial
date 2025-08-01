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
make ci-phpstan
make ci-phpunit
```
On every push to the branch, the CI/CD GitHub Actions will be triggered.

## New feature pull request

- All CI/CD GitHub Actions should be passing
- Pointed to latest release branch e.g. `release-2.0.0`
- Description should be clear and concise. Explain why this feature is needed or what do you want to achieve.

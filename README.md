# Laravel Pipeline Engine

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hatchyu/laravel-pipeline-engine.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-pipeline-engine)
[![Total Downloads](https://img.shields.io/packagist/dt/hatchyu/laravel-pipeline-engine.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-pipeline-engine)
[![License](https://img.shields.io/packagist/l/hatchyu/laravel-pipeline-engine.svg?style=flat-square)](LICENSE)

A reusable, configurable, and highly extensible CI/CD pipeline engine for Laravel projects. It provides centralized shell scripts for linting, security checking, and testing, along with a custom interactive Laravel Artisan installer command to configure GitHub Actions workflows in seconds.

---

## 🚀 Why Use Laravel Pipeline Engine?

Instead of copy-pasting hundreds of lines of GitHub Actions YAML configurations across all your Laravel projects, this package centralizes your pipeline logic in a single dependency.

- **Centralized Workflows:** Keep your CI/CD runner logic inside the package. When you update the package, all your projects instantly inherit the updates.
- **Interactive Scaffolding:** Run a single command to generate a pre-configured, optimized `.github/workflows/ci.yml` file customized for your project.
- **Zero-Configuration Fallbacks:** Built-in scripts dynamically detect and run Pint, Larastan, Pest, or PHPUnit only if they are installed.
- **Fast Testing:** Defaults to SQLite in-memory databases to make test suite runs blazing fast without external Docker container requirements.

---

## 📦 Installation

Install the package via Composer as a dev dependency:

```bash
composer require hatchyu/laravel-pipeline-engine --dev
```

---

## 🛠️ Getting Started

### 1. Run the Interactive Installer

Configure and scaffold your GitHub Actions workflow file by running:

```bash
php artisan pipeline:install
```

This interactive CLI will guide you through:
1. Selecting your PHP version.
2. Enabling/disabling frontend/Node.js asset building.
3. Deciding which checks (Quality, Security, Tests) to include in the pipeline.

The script creates/updates your `.github/workflows/ci.yml` file.

### 2. Push to GitHub

Commit the new workflow file and push to GitHub:

```bash
git add .github/workflows/ci.yml
git commit -m "chore: install hatchyu pipeline engine"
git push
```

---

## 🔍 Core Runners

The package registers three binaries under `vendor/bin/` which run automatically in CI, but can also be run locally:

### 1. `vendor/bin/ci-lint`
Checks code quality in three phases:
- **Syntax Check:** Parallel PHP syntax verification (`php -l`) across common source directories.
- **Code Styling:** Automatically runs Laravel Pint (`vendor/bin/pint --test`) or PHP-CS-Fixer if configured.
- **Static Analysis:** Runs Larastan/PHPStan (`vendor/bin/phpstan analyse`) if present.

### 2. `vendor/bin/ci-security`
Ensures dependency safety:
- **Composer Audit:** Runs native `composer audit` to scan dependencies for known vulnerabilities and stops deployment on failures.

### 3. `vendor/bin/ci-test`
Executes test suites seamlessly:
- **Environment Fallbacks:** Automatically configures `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` for lightning-fast testing.
- **Runner Detection:** Detects whether to use Laravel's standard `php artisan test`, Pest (`vendor/bin/pest`), or PHPUnit (`vendor/bin/phpunit`).
- **Command Arguments:** Forwards any additional arguments (e.g. running specific tests or coverage reports) directly to the underlying runner.

---

## 🎛️ Customizations & Overrides

### Environment Overrides

If your project requires a specific database (e.g. MySQL) or special env configurations for testing, configure them directly in your `.github/workflows/ci.yml` file or your `.env.testing`. The runners respect existing environment variables:

```yaml
      - name: Run Test Suite
        env:
          DB_CONNECTION: mysql
          DB_DATABASE: custom_testing_db
        run: ./vendor/bin/ci-test
```

### Adding Project-Specific Pipeline Steps

Since the GitHub Actions file lives in your project repository, you can seamlessly add custom actions (e.g. E2E tests, build steps, or boundary assertions) directly alongside the core runners:

```yaml
    # ==========================================
    # 1. RUN CORE PIPELINE
    # ==========================================
    - name: Run Quality Checks
      run: ./vendor/bin/ci-lint

    - name: Run Security Audits
      run: ./vendor/bin/ci-security

    - name: Run Test Suite
      run: ./vendor/bin/ci-test

    # ==========================================
    # 2. CUSTOM PROJECT-SPECIFIC STEPS
    # ==========================================
    - name: Run E2E Cypress Tests
      run: npm run test:e2e
```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

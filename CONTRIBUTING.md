# Contributing to Laravel Hosting Deploy

Thank you for considering contributing to Laravel Hosting Deploy! We welcome any contributions that make this package better.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Setting Up Development Environment](#setting-up-development-environment)
- [Coding Standards](#coding-standards)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

## Code of Conduct

Please be respectful and constructive. We aim to maintain a positive and inclusive community.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Laravel version** (e.g., 11.x, 12.x)
- **PHP version** (e.g., 8.2, 8.3)
- **Package version**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **Error messages** or logs

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When suggesting an enhancement:

- Use a clear and descriptive title
- Provide a detailed explanation of the enhancement
- Explain why this enhancement would be useful
- Consider including examples or use cases

## Setting Up Development Environment

1. **Fork and clone the repository:**

```bash
git clone https://github.com/your-username/laravel-hosting-deploy.git
cd laravel-hosting-deploy
```

2. **Install dependencies:**

```bash
composer install
```

3. **Run tests:**

```bash
composer test
```

4. **Run code formatting:**

```bash
composer format
```

## Coding Standards

This project follows the Laravel coding standards:

- **PHPStan** for static analysis
- **Laravel Pint** for code formatting
- **Pest** for testing

### Running Pint

```bash
vendor/bin/pint
```

### Running Pest

```bash
vendor/bin/pest

# With coverage
vendor/bin/pest --coverage
```

### Code Style

- Use PSR-12 coding standard
- Follow Laravel conventions
- Add type hints to all methods
- Add return types to all methods
- Use PHP 8.2+ features when appropriate

### Example

```php
<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Commands;

use Illuminate\Console\Command;

final class ExampleCommand extends Command
{
    protected $signature = 'example:command {arg}';
    protected $description = 'Example command description';

    public function handle(): int
    {
        $arg = $this->argument('arg');

        $this->info("Processing: {$arg}");

        return self::SUCCESS;
    }
}
```

## Submitting Changes

1. **Create a new branch:**

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

2. **Make your changes:**

- Write clean, readable code
- Add tests for new features
- Update documentation as needed
- Run `composer format` to format your code
- Run `composer test` to ensure tests pass

3. **Commit your changes:**

```bash
git add .
git commit -m "feat: add new feature"
# or
git commit -m "fix: resolve bug description"
```

Use conventional commits:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

4. **Push to your fork:**

```bash
git push origin feature/your-feature-name
```

5. **Create a Pull Request:**

- Provide a clear description of changes
- Reference related issues
- Include screenshots for UI changes
- Ensure all CI checks pass

## Pull Request Guidelines

- **One feature per PR** - Keep changes focused
- **Small PRs** are easier to review
- **Write clear commit messages**
- **Update documentation** for user-facing changes
- **Add tests** for new functionality
- **Ensure tests pass** before submitting

## Testing

Write tests for all new functionality:

```php
it('performs an action', function () {
    $result = app(YourClass::class)->performAction();

    expect($result)->toBeTrue();
});
```

## Questions?

Feel free to open an issue with the `question` label if you have any questions about contributing.

---

Thank you for your contributions! 🎉

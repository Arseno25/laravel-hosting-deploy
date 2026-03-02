# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of `laravel-hosting-deploy` package
- SSH deployment with password or key authentication
- GitHub Actions CI/CD setup automation
- GitHub API integration for deploy keys and secrets management
- ED25519 SSH key generation
- LibSodium encryption for GitHub secrets
- Deployment options: fresh, storage link, frontend building
- Dry-run mode for script preview
- Multiple deployment commands
- Comprehensive test suite with Pest
- Full documentation

### Commands
- `hosting-deploy:run` - Deploy application to server via SSH
- `hosting-deploy:setup-cicd` - Set up SSH keys and GitHub secrets
- `hosting-deploy:github-actions` - Generate GitHub Actions workflow
- `hosting-deploy:all` - All-in-one setup and deploy

## [1.0.0] - 2024-03-02

### Added
- Initial release
- Support for Laravel 10, 11, 12
- Support for PHP 8.1+
- SSH deployment to any hosting/VPS
- GitHub Actions integration
- Automated CI/CD setup
- Deploy key management
- GitHub secrets encryption
- Storage link management
- Frontend build automation
- Database migrations support
- Cache clearing and optimization
- Queue worker restart
- Laravel Horizon termination support

### Security
- SSH key authentication (recommended)
- ED25519 key algorithm
- LibSodium encryption for secrets
- Proper file permissions (0600)
- Read-only deploy keys

[Unreleased]: https://github.com/arseno25/laravel-hosting-deploy/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/arseno25/laravel-hosting-deploy/releases/tag/v1.0.0

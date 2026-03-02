<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Services;

use Illuminate\Support\Facades\Config;

class DeploymentProcess
{
    protected ?bool $fresh = null;

    protected ?bool $linkStorage = null;

    protected ?bool $buildFrontend = null;

    /**
     * Set fresh deployment option.
     */
    public function fresh(bool $fresh = true): self
    {
        $this->fresh = $fresh;

        return $this;
    }

    /**
     * Set storage link option.
     */
    public function linkStorage(bool $link = true): self
    {
        $this->linkStorage = $link;

        return $this;
    }

    /**
     * Set frontend build option.
     */
    public function buildFrontend(bool $build = true): self
    {
        $this->buildFrontend = $build;

        return $this;
    }

    /**
     * Build the deployment script to be executed on the remote server.
     */
    public function buildScript(): string
    {
        $projectDir = Config::get('hosting-deploy.deployment.project_dir');
        $githubToken = Config::get('hosting-deploy.github.token');
        $githubRepo = Config::get('hosting-deploy.github.repo');
        $branch = Config::get('hosting-deploy.github.default_branch', 'main');
        $composerFlags = Config::get('hosting-deploy.deployment.composer_flags', '--no-dev --optimize-autoloader');
        $runMigrations = Config::get('hosting-deploy.deployment.run_migrations', true);
        $runSeeders = Config::get('hosting-deploy.deployment.run_seeders', false);
        $clearCache = Config::get('hosting-deploy.deployment.clear_cache', true);
        $optimize = Config::get('hosting-deploy.deployment.optimize', true);

        $fresh = $this->fresh ?? Config::get('hosting-deploy.options.fresh', false);
        $linkStorage = $this->linkStorage ?? Config::get('hosting-deploy.options.link_storage', true);
        $buildFrontend = $this->buildFrontend ?? Config::get('hosting-deploy.options.build_frontend', true);

        if (empty($projectDir)) {
            throw new \InvalidArgumentException('Project directory is not configured.');
        }

        if (empty($githubToken) || empty($githubRepo)) {
            throw new \InvalidArgumentException('GitHub credentials are not configured.');
        }

        $commands = [];

        // Change to project directory
        $commands[] = sprintf('cd %s', escapeshellarg($projectDir));

        // Fresh deployment - remove vendor and node_modules first
        if ($fresh) {
            $commands[] = 'rm -rf vendor node_modules';
        }

        // Git pull with token authentication
        $gitUrl = sprintf('https://%s@github.com/%s.git', $githubToken, $githubRepo);
        $commands[] = sprintf('git pull %s %s', escapeshellarg($gitUrl), escapeshellarg($branch));

        // Install composer dependencies
        $commands[] = sprintf('composer install %s', $composerFlags);

        // Install npm dependencies and build frontend
        if ($buildFrontend) {
            if ($fresh) {
                $commands[] = 'npm ci';
            } else {
                $commands[] = 'npm install';
            }
            $commands[] = 'npm run build';
        }

        // Link storage if enabled
        if ($linkStorage) {
            $commands[] = 'php artisan storage:link';
        }

        // Run migrations if enabled
        if ($runMigrations) {
            if ($fresh) {
                $commands[] = 'php artisan migrate:fresh --force';
            } else {
                $commands[] = 'php artisan migrate --force';
            }
        }

        // Run seeders if enabled
        if ($runSeeders) {
            $commands[] = 'php artisan db:seed --force';
        }

        // Clear cache if enabled
        if ($clearCache) {
            $commands[] = 'php artisan cache:clear';
            $commands[] = 'php artisan config:clear';
            $commands[] = 'php artisan route:clear';
            $commands[] = 'php artisan view:clear';
            $commands[] = 'php artisan event:clear';
        }

        // Optimize if enabled
        if ($optimize) {
            $commands[] = 'php artisan config:cache';
            $commands[] = 'php artisan route:cache';
            $commands[] = 'php artisan view:cache';
            $commands[] = 'php artisan event:cache';
            $commands[] = 'php artisan optimize';
        }

        return implode(' && ', $commands);
    }

    /**
     * Build deployment script for GitHub Actions.
     */
    public function buildGitHubActionsScript(): string
    {
        $projectDir = Config::get('hosting-deploy.deployment.project_dir');
        $branch = Config::get('hosting-deploy.github.default_branch', 'main');
        $composerFlags = Config::get('hosting-deploy.deployment.composer_flags', '--no-dev --optimize-autoloader');
        $runMigrations = Config::get('hosting-deploy.deployment.run_migrations', true);
        $runSeeders = Config::get('hosting-deploy.deployment.run_seeders', false);
        $clearCache = Config::get('hosting-deploy.deployment.clear_cache', true);
        $optimize = Config::get('hosting-deploy.deployment.optimize', true);

        $fresh = $this->fresh ?? Config::get('hosting-deploy.options.fresh', false);
        $linkStorage = $this->linkStorage ?? Config::get('hosting-deploy.options.link_storage', true);
        $buildFrontend = $this->buildFrontend ?? Config::get('hosting-deploy.options.build_frontend', true);

        if (empty($projectDir)) {
            throw new \InvalidArgumentException('Project directory is not configured.');
        }

        $commands = [];

        // Change to project directory
        $commands[] = sprintf('cd %s', escapeshellarg($projectDir));

        // Fresh deployment
        if ($fresh) {
            $commands[] = 'rm -rf vendor node_modules';
        }

        // Git pull
        $commands[] = sprintf('git pull origin %s', escapeshellarg($branch));

        // Install composer dependencies
        $commands[] = sprintf('composer install %s', $composerFlags);

        // Install npm dependencies and build frontend
        if ($buildFrontend) {
            if ($fresh) {
                $commands[] = 'npm ci';
            } else {
                $commands[] = 'npm install';
            }
            $commands[] = 'npm run build';
        }

        // Link storage if enabled
        if ($linkStorage) {
            $commands[] = 'php artisan storage:link';
        }

        // Run migrations if enabled
        if ($runMigrations) {
            if ($fresh) {
                $commands[] = 'php artisan migrate:fresh --force';
            } else {
                $commands[] = 'php artisan migrate --force';
            }
        }

        // Run seeders if enabled
        if ($runSeeders) {
            $commands[] = 'php artisan db:seed --force';
        }

        // Clear cache if enabled
        if ($clearCache) {
            $commands[] = 'php artisan cache:clear';
            $commands[] = 'php artisan config:clear';
            $commands[] = 'php artisan route:clear';
            $commands[] = 'php artisan view:clear';
            $commands[] = 'php artisan event:clear';
        }

        // Optimize if enabled
        if ($optimize) {
            $commands[] = 'php artisan config:cache';
            $commands[] = 'php artisan route:cache';
            $commands[] = 'php artisan view:cache';
            $commands[] = 'php artisan event:cache';
            $commands[] = 'php artisan optimize';
        }

        return implode(' && ', $commands);
    }

    /**
     * Get a human-readable description of what will be deployed.
     */
    public function getDescription(): string
    {
        $projectDir = Config::get('hosting-deploy.deployment.project_dir');
        $githubRepo = Config::get('hosting-deploy.github.repo');
        $branch = Config::get('hosting-deploy.github.default_branch', 'main');
        $fresh = $this->fresh ?? Config::get('hosting-deploy.options.fresh', false);

        $description = sprintf(
            'Deploying %s (branch: %s) to %s',
            $githubRepo ?? 'unknown repository',
            $branch,
            $projectDir ?? 'unknown directory'
        );

        if ($fresh) {
            $description .= ' [FRESH - will reset database]';
        }

        return $description;
    }

    /**
     * Reset options to default config values.
     */
    public function resetOptions(): self
    {
        $this->fresh = null;
        $this->linkStorage = null;
        $this->buildFrontend = null;

        return $this;
    }
}

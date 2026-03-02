<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Services\DeploymentProcess;

beforeEach(function () {
    config([
        'hosting-deploy.server' => [
            'host' => 'test-server.com',
            'port' => 22,
            'username' => 'testuser',
            'password' => 'testpass',
            'timeout' => 30,
            'ssh_key_path' => null,
        ],
        'hosting-deploy.deployment' => [
            'project_dir' => '/var/www/html',
            'composer_flags' => '--no-dev --optimize-autoloader',
            'run_migrations' => true,
            'run_seeders' => false,
            'clear_cache' => true,
            'optimize' => true,
        ],
        'hosting-deploy.options' => [
            'fresh' => false,
            'link_storage' => true,
            'build_frontend' => true,
        ],
        'hosting-deploy.github' => [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
            'setup_ssh_keys' => false,
        ],
    ]);
});

describe('DeploymentProcess', function () {
    beforeEach(function () {
        $this->deployment = app(DeploymentProcess::class);
    });

    it('can be instantiated', function () {
        expect($this->deployment)->toBeInstanceOf(DeploymentProcess::class);
    });

    it('can set fresh option', function () {
        $result = $this->deployment->fresh();

        expect($result)->toBeInstanceOf(DeploymentProcess::class);
    });

    it('can set link storage option', function () {
        $result = $this->deployment->linkStorage(false);

        expect($result)->toBeInstanceOf(DeploymentProcess::class);
    });

    it('can set build frontend option', function () {
        $result = $this->deployment->buildFrontend(false);

        expect($result)->toBeInstanceOf(DeploymentProcess::class);
    });

    it('can reset options', function () {
        $this->deployment->fresh()->linkStorage(false)->buildFrontend(false);

        $result = $this->deployment->resetOptions();

        expect($result)->toBeInstanceOf(DeploymentProcess::class);
    });

    describe('buildScript', function () {
        it('builds default deployment script', function () {
            $script = $this->deployment->buildScript();

            expect($script)
                ->toBeString()
                ->toContain('cd "/var/www/html"')
                ->toContain('git pull')
                ->toContain('composer install')
                ->toContain('php artisan migrate --force');
        });

        it('builds fresh deployment script', function () {
            $this->deployment->fresh();

            $script = $this->deployment->buildScript();

            expect($script)
                ->toContain('rm -rf vendor node_modules')
                ->toContain('php artisan migrate:fresh --force');
        });

        it('builds script without storage link', function () {
            $this->deployment->linkStorage(false);

            $script = $this->deployment->buildScript();

            expect($script)->not->toContain('php artisan storage:link');
        });

        it('builds script without frontend build', function () {
            $this->deployment->buildFrontend(false);

            $script = $this->deployment->buildScript();

            expect($script)
                ->not->toContain('npm install')
                ->not->toContain('npm run build');
        });
    });

    describe('buildGitHubActionsScript', function () {
        it('builds GitHub Actions deployment script', function () {
            $script = $this->deployment->buildGitHubActionsScript();

            expect($script)
                ->toBeString()
                ->toContain('cd "/var/www/html"')
                ->toContain('git pull origin "main"')
                ->toContain('composer install')
                ->toContain('php artisan migrate --force');
        });
    });

    describe('getDescription', function () {
        it('returns deployment description', function () {
            $description = $this->deployment->getDescription();

            expect($description)
                ->toBeString()
                ->toContain('test/repo')
                ->toContain('main')
                ->toContain('/var/www/html');
        });

        it('includes fresh warning in description', function () {
            $this->deployment->fresh();

            $description = $this->deployment->getDescription();

            expect($description)->toContain('FRESH - will reset database');
        });
    });
});

<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Commands\DeployCommand;

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
        'hosting-deploy.github' => [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
        ],
    ]);
});

describe('DeployCommand', function () {
    it('has correct signature', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        expect($signatureProperty->getValue($command))->toContain('hosting-deploy:run');
    });

    it('has correct description', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);

        expect($descriptionProperty->getValue($command))->toBe('Deploy your Laravel application to remote server via SSH');
    });

    it('accepts --fresh option', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command->getDefinition()->hasOption('fresh'))->toBeTrue();
    });

    it('accepts --no-storage option', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command->getDefinition()->hasOption('no-storage'))->toBeTrue();
    });

    it('accepts --no-frontend option', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command->getDefinition()->hasOption('no-frontend'))->toBeTrue();
    });

    it('accepts --dry-run option', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command->getDefinition()->hasOption('dry-run'))->toBeTrue();
    });

    it('accepts --show-errors option', function () {
        $command = new DeployCommand(
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command->getDefinition()->hasOption('show-errors'))->toBeTrue();
    });

    describe('connection info', function () {
        it('displays connection details', function () {
            $service = app(Arseno25\HostingLaravelDeploy\Services\SSHService::class);
            $info = $service->getConnectionInfo();

            expect($info)
                ->toBeArray()
                ->toHaveKeys(['host', 'port', 'username', 'auth_method'])
                ->host->toBe('test-server.com')
                ->port->toBe(22)
                ->username->toBe('testuser');
        });
    });
});

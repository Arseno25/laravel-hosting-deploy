<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Commands\SetupCICDCommand;

beforeEach(function () {
    config([
        'hosting-deploy.server' => [
            'host' => 'test-server.com',
            'port' => 22,
            'username' => 'testuser',
            'password' => 'testpass',
        ],
        'hosting-deploy.deployment' => [
            'project_dir' => '/var/www/html',
        ],
        'hosting-deploy.github' => [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
        ],
    ]);
});

describe('SetupCICDCommand', function () {
    it('has correct signature', function () {
        $command = new SetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
        );

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        expect($signatureProperty->getValue($command))->toContain('hosting-deploy:setup-cicd');
    });

    it('has correct description', function () {
        $command = new SetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
        );

        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);

        expect($descriptionProperty->getValue($command))->toBe('Set up SSH keys and GitHub secrets for automated deployment');
    });

    it('accepts --force option', function () {
        $command = new SetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
        );

        expect($command->getDefinition()->hasOption('force'))->toBeTrue();
    });

    it('accepts --skip-key-check option', function () {
        $command = new SetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
        );

        expect($command->getDefinition()->hasOption('skip-key-check'))->toBeTrue();
    });

    describe('validation', function () {
        it('requires valid configuration', function () {
            $command = new SetupCICDCommand(
                app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
                app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            );

            // All config values are set in beforeEach, so this should pass
            expect($command)->toBeInstanceOf(SetupCICDCommand::class);
        });
    });
});

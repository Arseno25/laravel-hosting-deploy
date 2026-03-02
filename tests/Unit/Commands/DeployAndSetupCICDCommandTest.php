<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Commands\DeployAndSetupCICDCommand;

describe('DeployAndSetupCICDCommand', function () {
    it('has correct signature', function () {
        $command = new DeployAndSetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        expect($signatureProperty->getValue($command))->toContain('hosting-deploy:all');
    });

    it('has correct description', function () {
        $command = new DeployAndSetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);

        expect($descriptionProperty->getValue($command))->toBe('Set up CI/CD and deploy in one command');
    });

    it('accepts deployment options', function () {
        $command = new DeployAndSetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );
        $definition = $command->getDefinition();

        expect($definition->hasOption('fresh'))->toBeTrue();
        expect($definition->hasOption('no-storage'))->toBeTrue();
        expect($definition->hasOption('no-frontend'))->toBeTrue();
        expect($definition->hasOption('dry-run'))->toBeTrue();
        expect($definition->hasOption('show-errors'))->toBeTrue();
    });

    it('accepts CI/CD options', function () {
        $command = new DeployAndSetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );
        $definition = $command->getDefinition();

        expect($definition->hasOption('force'))->toBeTrue();
        expect($definition->hasOption('skip-key-check'))->toBeTrue();
    });

    it('is instance of Laravel command', function () {
        $command = new DeployAndSetupCICDCommand(
            app(Arseno25\HostingLaravelDeploy\Services\GitHubAPIService::class),
            app(Arseno25\HostingLaravelDeploy\Services\SSHService::class),
            app(Arseno25\HostingLaravelDeploy\Services\DeploymentProcess::class),
        );

        expect($command)->toBeInstanceOf(\Illuminate\Console\Command::class);
    });
});

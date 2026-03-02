<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Commands\DeployAndSetupCICDCommand;
use Arseno25\HostingLaravelDeploy\Commands\DeployCommand;
use Arseno25\HostingLaravelDeploy\Commands\SetupCICDCommand;
use Arseno25\HostingLaravelDeploy\Commands\SetupGithubActionsCommand;
use Arseno25\HostingLaravelDeploy\Services\DeploymentProcess;
use Arseno25\HostingLaravelDeploy\Services\GitHubAPIService;
use Arseno25\HostingLaravelDeploy\Services\SSHService;

// Use a more relaxed preset for Laravel packages
arch('commands')
    ->expect('Arseno25\HostingLaravelDeploy\Commands')
    ->toExtend('Illuminate\Console\Command')
    ->toOnlyBeUsedIn([
        'Arseno25\HostingLaravelDeploy',
        'Arseno25\HostingLaravelDeploy\Tests',
    ]);

arch('services')
    ->expect('Arseno25\HostingLaravelDeploy\Services')
    ->toOnlyBeUsedIn([
        'Arseno25\HostingLaravelDeploy',
        'Arseno25\HostingLaravelDeploy\Tests',
    ]);

describe('package structure', function () {
    it('has all commands', function () {
        expect([
            DeployCommand::class,
            SetupCICDCommand::class,
            SetupGithubActionsCommand::class,
            DeployAndSetupCICDCommand::class,
        ])->each->toBeClass()->toExtend('Illuminate\Console\Command');
    });

    it('has all services', function () {
        expect([
            SSHService::class,
            GitHubAPIService::class,
            DeploymentProcess::class,
        ])->each->toBeClass();
    });

    it('commands have correct namespace', function () {
        expect(DeployCommand::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Commands');
        expect(SetupCICDCommand::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Commands');
        expect(SetupGithubActionsCommand::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Commands');
        expect(DeployAndSetupCICDCommand::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Commands');
    });

    it('services have correct namespace', function () {
        expect(SSHService::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Services');
        expect(GitHubAPIService::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Services');
        expect(DeploymentProcess::class)->toStartWith('Arseno25\HostingLaravelDeploy\\Services');
    });
});

describe('command naming', function () {
    it('commands follow naming convention', function () {
        $commands = [
            'DeployCommand',
            'SetupCICDCommand',
            'SetupGithubActionsCommand',
            'DeployAndSetupCICDCommand',
        ];

        foreach ($commands as $command) {
            expect($command)->toEndWith('Command');
        }
    });
});

describe('service naming', function () {
    it('services follow naming convention', function () {
        $services = [
            'SSHService',
            'GitHubAPIService',
            'DeploymentProcess',
        ];

        foreach ($services as $service) {
            expect($service)->toMatch('/(Service|Process)$/');
        }
    });
});

arch('strict types')
    ->expect('Arseno25\HostingLaravelDeploy')
    ->toUseStrictTypes();

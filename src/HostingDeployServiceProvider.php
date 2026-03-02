<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy;

use Arseno25\HostingLaravelDeploy\Commands\DeployAndSetupCICDCommand;
use Arseno25\HostingLaravelDeploy\Commands\DeployCommand;
use Arseno25\HostingLaravelDeploy\Commands\SetupCICDCommand;
use Arseno25\HostingLaravelDeploy\Commands\SetupGithubActionsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HostingDeployServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('hosting-laravel-deploy')
            ->hasConfigFile(__DIR__.'/../config/hosting-deploy.php')
            ->hasCommands([
                DeployCommand::class,
                SetupGithubActionsCommand::class,
                SetupCICDCommand::class,
                DeployAndSetupCICDCommand::class,
            ]);
    }
}

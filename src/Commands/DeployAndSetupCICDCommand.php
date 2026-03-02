<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Commands;

use Illuminate\Console\Command;
use Arseno25\HostingLaravelDeploy\Services\GitHubAPIService;
use Arseno25\HostingLaravelDeploy\Services\SSHService;
use Arseno25\HostingLaravelDeploy\Services\DeploymentProcess;
use Illuminate\Support\Facades\Config;

class DeployAndSetupCICDCommand extends Command
{
    protected $signature = 'hosting-deploy:deploy-and-setup-cicd
        {--fresh : Perform a fresh deployment (reset database)}
        {--no-storage : Skip storage linking}
        {--no-frontend : Skip frontend building}
        {--force : Overwrite existing secrets}
        {--skip-key-check : Skip checking if deploy key already exists}
        {--dry-run : Show the deployment script without executing}
        {--show-errors : Show full error output from SSH command}
    ';

    protected $description = 'Set up CI/CD and deploy in one command';

    public function __construct(
        protected GitHubAPIService $github,
        protected SSHService $ssh,
        protected DeploymentProcess $deploymentProcess
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  🚀 Laravel Deploy - One-Command Setup & Deploy  ');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // Step 1: Setup CI/CD
        $this->info('📦 Step 1: Setting up CI/CD...');
        $this->newLine();

        $setupResult = $this->call('hosting-deploy:setup-cicd', [
            '--force' => $this->option('force'),
            '--skip-key-check' => $this->option('skip-key-check'),
        ]);

        if ($setupResult !== self::SUCCESS) {
            $this->error('❌ CI/CD setup failed. Aborting deployment.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ CI/CD setup completed successfully!');
        $this->newLine();

        // Step 2: Deploy
        $this->info('🚀 Step 2: Deploying application...');
        $this->newLine();

        $deployOptions = [
            '--fresh' => $this->option('fresh'),
            '--no-storage' => $this->option('no-storage'),
            '--no-frontend' => $this->option('no-frontend'),
            '--dry-run' => $this->option('dry-run'),
            '--show-errors' => $this->option('show-errors'),
        ];

        $deployResult = $this->call('hosting-deploy:run', $deployOptions);

        $this->newLine();

        if ($deployResult === self::SUCCESS) {
            $this->info('═══════════════════════════════════════════════════');
            $this->info('       ✅ Setup & Deployment Complete!            ');
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();
            $this->comment('🎉 Your application is now deployed and GitHub Actions');
            $this->comment('   is configured for future deployments!');
            $this->newLine();
            $this->comment('📌 Next time, simply push to your configured branch');
            $this->comment('   and GitHub Actions will handle the deployment.');
        } else {
            $this->error('❌ Deployment failed. Please check the errors above.');
        }

        return $deployResult;
    }
}

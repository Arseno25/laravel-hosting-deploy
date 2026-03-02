<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Commands;

use Illuminate\Console\Command;
use Arseno25\HostingLaravelDeploy\Services\SSHService;
use Arseno25\HostingLaravelDeploy\Services\DeploymentProcess;
use Illuminate\Support\Facades\Config;

class DeployCommand extends Command
{
    protected $signature = 'hosting-deploy:run
        {--fresh : Perform a fresh deployment (reset database)}
        {--no-storage : Skip storage linking}
        {--no-frontend : Skip frontend building}
        {--dry-run : Show the deployment script without executing}
        {--show-errors : Show full error output from SSH command}
    ';

    protected $description = 'Deploy your Laravel application to remote server via SSH';

    public function __construct(
        protected SSHService $ssh,
        protected DeploymentProcess $deploymentProcess
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('      🚀 Laravel Deployment to Server            ');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        try {
            // Apply deployment options
            if ($this->option('fresh')) {
                $this->deploymentProcess->fresh();
            }

            if ($this->option('no-storage')) {
                $this->deploymentProcess->linkStorage(false);
            }

            if ($this->option('no-frontend')) {
                $this->deploymentProcess->buildFrontend(false);
            }

            // Show connection info
            $this->info('📡 Connection Details:');
            $info = $this->ssh->getConnectionInfo();
            foreach ($info as $key => $value) {
                if ($key === 'password' || $key === 'ssh_key_path') {
                    continue;
                }
                $this->line("  {$key}: {$value}");
            }
            $this->newLine();

            // Show deployment info
            $this->info('📦 ' . $this->deploymentProcess->getDescription());

            if ($this->option('fresh')) {
                $this->warn('  ⚠️  WARNING: This will reset the database!');
                if (!$this->confirm('  Are you sure you want to continue?', false)) {
                    $this->info('❌ Deployment cancelled.');

                    return self::INVALID;
                }
            } else {
                $this->newLine();
            }

            // Build and show deployment script
            $script = $this->deploymentProcess->buildScript();

            if ($this->option('verbose') || $this->option('dry-run')) {
                $this->newLine();
                $this->info('📜 Deployment script:');
                $this->line(str_repeat('-', 60));
                $this->line($script);
                $this->line(str_repeat('-', 60));
                $this->newLine();
            }

            // Dry run - don't execute
            if ($this->option('dry-run')) {
                $this->info('✅ Dry run complete. No changes were made.');

                return self::SUCCESS;
            }

            // Test connection first
            $this->info('🔌 Testing SSH connection...');

            if (!$this->ssh->testConnection()) {
                $this->error('❌ SSH connection test failed.');
                $this->error('Please check your credentials and SSH key configuration.');
                $this->newLine();
                $this->comment('💡 Tip: Run php artisan hosting-deploy:setup-cicd to set up SSH keys.');

                return self::FAILURE;
            }

            $this->info('✅ Connection test passed.');
            $this->newLine();

            // Execute deployment
            $this->info('🚀 Executing deployment...');
            $this->newLine();

            $showErrors = $this->option('show-errors');

            $this->task('Running deployment script', function () use ($script, $showErrors) {
                $result = $this->ssh->execute($script, [], $showErrors);

                if ($showErrors && is_array($result)) {
                    $this->newLine();
                    if (!empty($result['output'])) {
                        $this->line('Output:');
                        $this->line($result['output']);
                    }
                    if (!empty($result['error_output'])) {
                        $this->line('Error Output:');
                        $this->line($result['error_output']);
                    }
                } elseif (!empty($result)) {
                    $this->newLine();
                    $this->line($result);
                }

                return true;
            });

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════');
            $this->info('      ✅ Deployment completed successfully!       ');
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();

            // Show connection info for SSH key users
            if ($this->ssh->hasSSHKey()) {
                $this->comment('🔐 Using SSH key authentication.');
            } else {
                $this->comment('🔑 Using password authentication.');
                $this->comment('💡 Consider using SSH keys for better security:');
                $this->comment('     php artisan hosting-deploy:setup-cicd');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════════════════');
            $this->error('      ❌ Deployment failed!                     ');
            $this->error('═══════════════════════════════════════════════════');
            $this->error($e->getMessage());
            $this->newLine();

            if ($this->option('verbose') || $this->option('show-errors')) {
                $this->error('📚 Stack trace:');
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        } finally {
            // Reset deployment options
            $this->deploymentProcess->resetOptions();
        }
    }
}

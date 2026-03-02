<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetupGithubActionsCommand extends Command
{
    protected $signature = 'hosting-deploy:github-actions';

    protected $description = 'Generate GitHub Actions workflow file for automated deployment';

    public function handle(): int
    {
        $branch = Config::get('hosting-deploy.github.default_branch', 'main');

        $stubPath = __DIR__ . '/../../stubs/github-actions-deploy.stub';

        if (!file_exists($stubPath)) {
            $this->error('❌ GitHub Actions stub file not found.');

            return self::FAILURE;
        }

        $workflowContent = file_get_contents($stubPath);
        $workflowContent = str_replace('{{ branch }}', $branch, $workflowContent);

        $githubWorkflowsDir = base_path('.github/workflows');

        if (!is_dir($githubWorkflowsDir)) {
            mkdir($githubWorkflowsDir, 0755, true);
        }

        $workflowPath = $githubWorkflowsDir . '/hosting-deploy.yml';

        if (file_put_contents($workflowPath, $workflowContent)) {
            $this->info('✅ GitHub Actions workflow file created successfully!');
            $this->info("📁 Path: {$workflowPath}");
            $this->newLine();
            $this->comment('For password authentication, add these secrets to your GitHub repository:');
            $this->comment('  • DEPLOY_HOST');
            $this->comment('  • DEPLOY_PORT');
            $this->comment('  • DEPLOY_USERNAME');
            $this->comment('  • DEPLOY_PASSWORD');
            $this->comment('  • DEPLOY_PROJECT_DIR');
            $this->newLine();
            $this->comment('For SSH key authentication (recommended):');
            $this->comment('  • DEPLOY_HOST');
            $this->comment('  • DEPLOY_PORT');
            $this->comment('  • DEPLOY_USERNAME');
            $this->comment('  • DEPLOY_SSH_KEY (your private SSH key content)');
            $this->comment('  • DEPLOY_PROJECT_DIR');
            $this->newLine();
            $this->comment('💡 To set up SSH keys automatically, run:');
            $this->comment('     php artisan hosting-deploy:setup-cicd');

            return self::SUCCESS;
        }

        $this->error('❌ Failed to create GitHub Actions workflow file.');

        return self::FAILURE;
    }
}

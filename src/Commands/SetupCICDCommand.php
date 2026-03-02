<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Commands;

use Illuminate\Console\Command;
use Arseno25\HostingLaravelDeploy\Services\GitHubAPIService;
use Arseno25\HostingLaravelDeploy\Services\SSHService;
use Illuminate\Support\Facades\Config;

class SetupCICDCommand extends Command
{
    protected $signature = 'hosting-deploy:setup-cicd
        {--force : Overwrite existing secrets}
        {--skip-key-check : Skip checking if deploy key already exists}
    ';

    protected $description = 'Set up SSH keys and GitHub secrets for automated deployment';

    public function __construct(
        protected GitHubAPIService $github,
        protected SSHService $ssh
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('      🚀 CI/CD Deployment Setup                  ');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // Validate configuration
        $this->validateConfiguration();

        try {
            // Step 1: Generate SSH key pair
            $this->info('🔑 Step 1: Generating SSH key pair...');

            $keyData = $this->github->generateSSHKeyPair();

            $this->info("  ✅ Generated SSH key: {$keyData['name']}");
            $this->info("  📋 Public key: " . substr($keyData['public_key'], 0, 50) . "...");
            $this->newLine();

            // Step 2: Add deploy key to GitHub repository
            $this->info('🔗 Step 2: Adding deploy key to GitHub repository...');

            if (!$this->option('skip-key-check') && $this->github->deployKeyExists($keyData['public_key'])) {
                $this->warn('  ⚠️  Deploy key already exists in the repository.');

                if (!$this->confirm('  Do you want to continue anyway?', true)) {
                    return self::FAILURE;
                }
            } else {
                if ($this->github->addDeployKey(config('app.name') . ' Deploy Key', $keyData['public_key'])) {
                    $this->info('  ✅ Deploy key added successfully.');
                } else {
                    $this->error('  ❌ Failed to add deploy key.');
                    $this->error('  You may need to add it manually in GitHub repository settings.');

                    $this->newLine();
                    $this->comment('Deploy key to add manually:');
                    $this->line($keyData['public_key']);
                }
            }
            $this->newLine();

            // Step 3: Store private key locally
            $this->info('💾 Step 3: Storing SSH private key...');

            $keyPath = $this->github->storeSSHKey($keyData);

            $this->info("  ✅ Private key stored at: {$keyPath}");
            $this->newLine();

            // Step 4: Add public key to server's authorized_keys
            $this->info('🔐 Step 4: Adding public key to server...');

            try {
                $this->ssh->addAuthorizedKey($keyData['public_key']);
                $this->info('  ✅ Public key added to server authorized_keys.');
            } catch (\Exception $e) {
                $this->warn('  ⚠️  Could not add key to server automatically.');
                $this->warn('  Add this key manually to ~/.ssh/authorized_keys on your server:');

                $this->newLine();
                $this->comment($keyData['public_key']);
            }
            $this->newLine();

            // Step 5: Set GitHub secrets
            $this->info('🔒 Step 5: Setting up GitHub secrets...');

            $secrets = [
                'DEPLOY_HOST' => Config::get('hosting-deploy.server.host'),
                'DEPLOY_PORT' => (string) Config::get('hosting-deploy.server.port', 22),
                'DEPLOY_USERNAME' => Config::get('hosting-deploy.server.username'),
                'DEPLOY_PROJECT_DIR' => Config::get('hosting-deploy.deployment.project_dir'),
                'DEPLOY_SSH_KEY' => $keyData['private_key'],
            ];

            foreach ($secrets as $secretName => $secretValue) {
                if (empty($secretValue)) {
                    $this->warn("  ⏭️  Skipping {$secretName} (not configured)");
                    continue;
                }

                if (!$this->option('force') && $this->github->secretExists($secretName)) {
                    $this->warn("  ⚠️  Secret {$secretName} already exists.");
                } else {
                    if ($this->github->setRepoSecret($secretName, $secretValue)) {
                        $this->info("  ✅ Set secret: {$secretName}");
                    } else {
                        $this->error("  ❌ Failed to set secret: {$secretName}");
                    }
                }
            }
            $this->newLine();

            // Step 6: Create GitHub Actions workflow
            $this->info('📝 Step 6: Creating GitHub Actions workflow...');

            $createCommand = $this->call('hosting-deploy:github-actions');

            if ($createCommand === self::SUCCESS) {
                $this->info('  ✅ GitHub Actions workflow created.');
            } else {
                $this->error('  ❌ Failed to create GitHub Actions workflow.');
            }
            $this->newLine();

            // Summary
            $this->info('═══════════════════════════════════════════════════');
            $this->info('         ✅ CI/CD Setup Complete!                 ');
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();
            $this->comment('📌 Next steps:');
            $this->comment('  1️⃣  Commit and push the .github/workflows/hosting-deploy.yml file');
            $this->comment('  2️⃣  Verify secrets in GitHub repository settings (Actions > Secrets)');
            $this->comment('  3️⃣  Push to your configured branch to trigger deployment');
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Setup failed!');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function validateConfiguration(): void
    {
        $required = [
            'hosting-deploy.server.host' => 'DEPLOY_HOST',
            'hosting-deploy.server.username' => 'DEPLOY_USERNAME',
            'hosting-deploy.github.token' => 'DEPLOY_GITHUB_TOKEN',
            'hosting-deploy.github.repo' => 'DEPLOY_REPO',
            'hosting-deploy.deployment.project_dir' => 'DEPLOY_PROJECT_DIR',
        ];

        $missing = [];

        foreach ($required as $configKey => $envKey) {
            if (empty(Config::get($configKey))) {
                $missing[] = $envKey;
            }
        }

        if (!empty($missing)) {
            $this->error('Missing required configuration:');
            foreach ($missing as $envKey) {
                $this->error("  - {$envKey}");
            }
            $this->newLine();
            $this->error('Please set these values in your .env file and run this command again.');

            throw new \RuntimeException('Configuration validation failed.');
        }
    }
}

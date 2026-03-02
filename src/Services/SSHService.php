<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\InvokedProcess;
use Symfony\Component\Process\Process as SymfonyProcess;

class SSHService
{
    protected ?string $host;

    protected int $port;

    protected ?string $username;

    protected ?string $password;

    protected ?string $sshKeyPath;

    protected int $timeout;

    public function __construct()
    {
        $this->host = Config::get('hosting-deploy.server.host');
        $this->port = (int) Config::get('hosting-deploy.server.port', 22);
        $this->username = Config::get('hosting-deploy.server.username');
        $this->password = Config::get('hosting-deploy.server.password');
        $this->sshKeyPath = Config::get('hosting-deploy.server.ssh_key_path');
        $this->timeout = (int) Config::get('hosting-deploy.server.timeout', 30);
    }

    /**
     * Execute a command on the remote server via SSH.
     *
     * @param array<string, mixed> $options
     * @return array<string, string>|string
     * @throws \Exception
     */
    public function execute(string $command, array $options = [], bool $returnErrors = false): string|array
    {
        $sshCommand = $this->buildSSHCommand($command, $options);

        $process = Process::timeout($this->timeout)->run($sshCommand);

        if ($process->failed()) {
            throw new \Exception(
                "SSH command failed: {$process->errorOutput()}",
                $process->exitCode() ?? 1
            );
        }

        if ($returnErrors) {
            return [
                'output' => $process->output(),
                'error_output' => $process->errorOutput(),
            ];
        }

        return $process->output();
    }

    /**
     * Execute a command asynchronously.
     *
     * @param array<string, mixed> $options
     */
    public function executeAsync(string $command, array $options = []): InvokedProcess
    {
        $sshCommand = $this->buildSSHCommand($command, $options);

        return Process::timeout($this->timeout)->start($sshCommand);
    }

    /**
     * Build the SSH command string.
     *
     * @param array<string, mixed> $options
     */
    protected function buildSSHCommand(string $command, array $options = []): string
    {
        $sshOptions = [];

        // Add strict host key checking option
        $sshOptions[] = '-o StrictHostKeyChecking=no';
        $sshOptions[] = '-o UserKnownHostsFile=/dev/null';

        // Add custom timeout
        $sshOptions[] = sprintf('-o ConnectTimeout=%d', $this->timeout);

        // Use SSH key if available
        if ($this->sshKeyPath && file_exists($this->sshKeyPath)) {
            $sshOptions[] = sprintf('-i %s', escapeshellarg($this->sshKeyPath));
        }

        // Add any custom options
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $sshOptions[] = "-{$key}";
                }
            } else {
                $sshOptions[] = "-{$key} " . escapeshellarg((string) $value);
            }
        }

        $optionsString = implode(' ', $sshOptions);
        $userHost = sprintf('%s@%s', escapeshellarg($this->username ?? ''), escapeshellarg($this->host ?? ''));
        $escapedCommand = escapeshellarg($command);

        return sprintf('ssh %s %s %s', $optionsString, $userHost, $escapedCommand);
    }

    /**
     * Test the SSH connection.
     */
    public function testConnection(): bool
    {
        try {
            $this->execute('echo "connection_test"');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if SSH key authentication is available.
     */
    public function hasSSHKey(): bool
    {
        if ($this->sshKeyPath && file_exists($this->sshKeyPath)) {
            return true;
        }

        // Check default storage location
        $defaultKeyPath = storage_path('app/deploy/id_deploy');

        return file_exists($defaultKeyPath);
    }

    /**
     * Get the SSH key path.
     */
    public function getSSHKeyPath(): ?string
    {
        if ($this->sshKeyPath && file_exists($this->sshKeyPath)) {
            return $this->sshKeyPath;
        }

        $defaultKeyPath = storage_path('app/deploy/id_deploy');

        if (file_exists($defaultKeyPath)) {
            return $defaultKeyPath;
        }

        return null;
    }

    /**
     * Check if password authentication is configured.
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * Get connection information for display purposes.
     *
     * @return array<string, mixed>
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->host ?? 'not configured',
            'port' => $this->port,
            'username' => $this->username ?? 'not configured',
            'auth_method' => $this->hasSSHKey() ? 'ssh_key' : ($this->hasPassword() ? 'password' : 'none'),
            'ssh_key_path' => $this->getSSHKeyPath(),
        ];
    }

    /**
     * Create SSH directory structure if it doesn't exist.
     */
    public function ensureSSHDirectory(): void
    {
        $sshDir = storage_path('app/deploy');

        if (!is_dir($sshDir)) {
            mkdir($sshDir, 0755, true);
        }
    }

    /**
     * Store SSH private key content to file.
     */
    public function storeSSHKey(string $privateKey): string
    {
        $this->ensureSSHDirectory();

        $keyPath = storage_path('app/deploy/id_deploy');

        file_put_contents($keyPath, $privateKey);
        chmod($keyPath, 0600);

        return $keyPath;
    }

    /**
     * Upload a file to the remote server via SCP.
     */
    public function uploadFile(string $localPath, string $remotePath): void
    {
        $keyOption = '';
        if ($keyPath = $this->getSSHKeyPath()) {
            $keyOption = sprintf('-i %s', escapeshellarg($keyPath));
        }

        $userHost = sprintf('%s@%s', $this->username ?? '', $this->host ?? '');

        $command = sprintf(
            'scp %s -o StrictHostKeyChecking=no -P %d %s %s:%s',
            $keyOption,
            $this->port,
            escapeshellarg($localPath),
            escapeshellarg($userHost),
            escapeshellarg($remotePath)
        );

        $process = Process::timeout($this->timeout)->run($command);

        if ($process->failed()) {
            throw new \Exception(
                "SCP upload failed: {$process->errorOutput()}",
                $process->exitCode() ?? 1
            );
        }
    }

    /**
     * Download a file from the remote server via SCP.
     */
    public function downloadFile(string $remotePath, string $localPath): void
    {
        $keyOption = '';
        if ($keyPath = $this->getSSHKeyPath()) {
            $keyOption = sprintf('-i %s', escapeshellarg($keyPath));
        }

        $userHost = sprintf('%s@%s', $this->username ?? '', $this->host ?? '');

        $command = sprintf(
            'scp %s -o StrictHostKeyChecking=no -P %d %s:%s %s',
            $keyOption,
            $this->port,
            escapeshellarg($userHost),
            escapeshellarg($remotePath),
            escapeshellarg($localPath)
        );

        $process = Process::timeout($this->timeout)->run($command);

        if ($process->failed()) {
            throw new \Exception(
                "SCP download failed: {$process->errorOutput()}",
                $process->exitCode() ?? 1
            );
        }
    }

    /**
     * Create an authorized_keys entry from a public key.
     */
    public function addAuthorizedKey(string $publicKey): void
    {
        $homeDir = $this->execute('echo $HOME');
        $sshDir = trim($homeDir) . '/.ssh';
        $authKeysFile = $sshDir . '/authorized_keys';

        // Create .ssh directory if it doesn't exist
        $this->execute(sprintf('mkdir -p %s', escapeshellarg($sshDir)));
        $this->execute(sprintf('chmod 700 %s', escapeshellarg($sshDir)));

        // Add the key if it doesn't exist
        $checkCommand = sprintf('grep -q "%s" %s 2>/dev/null || true', addslashes($publicKey), escapeshellarg($authKeysFile));
        $result = trim($this->execute($checkCommand));

        if (empty($result)) {
            $addCommand = sprintf('echo "%s" >> %s', addslashes($publicKey), escapeshellarg($authKeysFile));
            $this->execute($addCommand);
            $this->execute(sprintf('chmod 600 %s', escapeshellarg($authKeysFile)));
        }
    }
}

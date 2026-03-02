<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Services;

use Exception;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Config;

class ServerConnectionService
{
    protected ?SSH2 $ssh = null;

    /**
     * Connect to the SSH server using credentials from config.
     *
     * @throws Exception
     */
    public function connect(): void
    {
        $host = Config::get('hosting-deploy.server.host');
        $port = (int) Config::get('hosting-deploy.server.port', 22);
        $username = Config::get('hosting-deploy.server.username');
        $password = Config::get('hosting-deploy.server.password');
        $timeout = (int) Config::get('hosting-deploy.server.timeout', 30);

        if (empty($host) || empty($username) || empty($password)) {
            throw new Exception('Incomplete SSH credentials. Please check your .env file.');
        }

        $this->ssh = new SSH2($host, $port, $timeout);

        if (!$this->ssh->login($username, $password)) {
            throw new Exception('SSH authentication failed. Please verify your credentials.');
        }
    }

    /**
     * Execute a command on the remote server.
     *
     * @throws Exception
     */
    public function executeCommand(string $command): string
    {
        if ($this->ssh === null) {
            throw new Exception('SSH connection not established. Call connect() first.');
        }

        $output = $this->ssh->exec($command);

        if ($this->ssh->getExitStatus() !== 0) {
            throw new Exception("Command execution failed: {$output}");
        }

        return $output;
    }

    /**
     * Disconnect from the SSH server.
     */
    public function disconnect(): void
    {
        if ($this->ssh !== null) {
            $this->ssh->disconnect();
            $this->ssh = null;
        }
    }

    /**
     * Check if currently connected.
     */
    public function isConnected(): bool
    {
        return $this->ssh !== null;
    }
}

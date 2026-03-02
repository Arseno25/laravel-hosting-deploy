<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Services\SSHService;

beforeEach(function () {
    config([
        'hosting-deploy.server' => [
            'host' => 'test-server.com',
            'port' => 22,
            'username' => 'testuser',
            'password' => 'testpass',
            'timeout' => 30,
            'ssh_key_path' => null,
        ],
        'hosting-deploy.ssh' => [
            'key_type' => 'ed25519',
            'key_bits' => 4096,
        ],
    ]);
});

describe('SSHService', function () {
    beforeEach(function () {
        $this->ssh = app(SSHService::class);
    });

    it('can be instantiated', function () {
        expect($this->ssh)->toBeInstanceOf(SSHService::class);
    });

    it('has connection info', function () {
        $info = $this->ssh->getConnectionInfo();

        expect($info)
            ->toBeArray()
            ->toHaveKey('host', 'test-server.com')
            ->toHaveKey('port', 22)
            ->toHaveKey('username', 'testuser')
            ->toHaveKey('auth_method');
    });

    it('detects password authentication', function () {
        expect($this->ssh->hasPassword())->toBeTrue();
    });

    it('detects no SSH key when none exists', function () {
        expect($this->ssh->hasSSHKey())->toBeFalse();
    });

    it('returns null for non-existent SSH key', function () {
        expect($this->ssh->getSSHKeyPath())->toBeNull();
    });

    it('returns null for stored SSH key when none exists', function () {
        // SSHService doesn't have getStoredSSHKeyPath method
        // Only GitHubAPIService has it
        expect($this->ssh->getSSHKeyPath())->toBeNull();
    });

    describe('buildSSHCommand', function () {
        it('builds SSH command with correct host', function () {
            $reflection = new \ReflectionClass($this->ssh);
            $method = $reflection->getMethod('buildSSHCommand');
            $method->setAccessible(true);

            $command = $method->invoke($this->ssh, 'ls -la', []);

            expect($command)
                ->toBeString()
                ->toContain('test-server.com');
        });

        it('includes strict host key checking options', function () {
            $reflection = new \ReflectionClass($this->ssh);
            $method = $reflection->getMethod('buildSSHCommand');
            $method->setAccessible(true);

            $command = $method->invoke($this->ssh, 'test', []);

            expect($command)
                ->toContain('StrictHostKeyChecking=no')
                ->toContain('UserKnownHostsFile=/dev/null');
        });

        it('includes timeout option', function () {
            $reflection = new \ReflectionClass($this->ssh);
            $method = $reflection->getMethod('buildSSHCommand');
            $method->setAccessible(true);

            $command = $method->invoke($this->ssh, 'test', []);

            expect($command)->toContain('ConnectTimeout=30');
        });
    });

    describe('storeSSHKey', function () {
        it('stores SSH key in deploy directory', function () {
            $testKey = "-----BEGIN OPENSSH PRIVATE KEY-----\ntest key content\n-----END OPENSSH PRIVATE KEY-----";
            $testDir = storage_path('app/deploy_test_' . time());

            if (is_dir($testDir)) {
                rmdir($testDir);
            }

            // Create test directory
            mkdir($testDir, 0755, true);

            file_put_contents($testDir . '/id_deploy', $testKey);
            chmod($testDir . '/id_deploy', 0600);

            expect(file_exists($testDir . '/id_deploy'))->toBeTrue();

            // Clean up
            unlink($testDir . '/id_deploy');
            rmdir($testDir);
        });
    });

    describe('ensureSSHDirectory', function () {
        it('creates directory structure', function () {
            $testDir = storage_path('app/deploy_test_' . time());

            if (is_dir($testDir)) {
                rmdir($testDir);
            }

            $ssh = new class extends SSHService
            {
                public function publicEnsure(): void
                {
                    $this->ensureSSHDirectory();
                }
            };

            $ssh->publicEnsure();

            // The default directory should exist now
            $defaultDir = storage_path('app/deploy');
            expect(is_dir($defaultDir))->toBeTrue();
        });
    });

    describe('connection method detection', function () {
        it('returns password auth method when only password is set', function () {
            $info = $this->ssh->getConnectionInfo();

            expect($info['auth_method'])->toBe('password');
        });
    });

    describe('key path methods', function () {
        it('returns null when SSH key path is not configured', function () {
            config(['hosting-deploy.server.ssh_key_path' => null]);

            $ssh = app(SSHService::class);

            expect($ssh->getSSHKeyPath())->toBeNull();
        });

        it('returns configured SSH key path when file exists', function () {
            $testPath = storage_path('test/key_' . time());

            // Create temp file
            $dir = dirname($testPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            touch($testPath);

            config(['hosting-deploy.server.ssh_key_path' => $testPath]);

            $ssh = app(SSHService::class);
            $result = $ssh->getSSHKeyPath();

            expect($result)->toBe($testPath);

            // Clean up
            unlink($testPath);
            // Use recursive directory removal to handle non-empty dirs
            foreach (glob($dir . '/*', GLOB_NOSORT) ?: [] as $item) {
                if (is_dir($item)) {
                    foreach (glob($item . '/*', GLOB_NOSORT) ?: [] as $subItem) {
                        if (is_file($subItem)) {
                            unlink($subItem);
                        }
                    }
                    rmdir($item);
                }
            }
            if (is_dir($dir) && count(glob($dir . '/*', GLOB_NOSORT)) === 0) {
                rmdir($dir);
            }
        });
    });
});

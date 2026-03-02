<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Services\GitHubAPIService;

beforeEach(function () {
    config([
        'hosting-deploy.github' => [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
            'setup_ssh_keys' => false,
        ],
        'hosting-deploy.ssh' => [
            'key_type' => 'ed25519',
            'key_bits' => 4096,
        ],
    ]);
});

describe('GitHubAPIService', function () {
    beforeEach(function () {
        $this->github = app(GitHubAPIService::class);
    });

    it('can be instantiated', function () {
        expect($this->github)->toBeInstanceOf(GitHubAPIService::class);
    });

    // Note: HTTP-dependent tests are skipped due to Orchestra Testbench HTTP fake issues
    // These should be tested in integration tests with actual HTTP mocking
    describe('storeSSHKey', function () {
        it('stores SSH key and returns path', function () {
            $keyData = [
                'private_key' => "-----BEGIN OPENSSH PRIVATE KEY-----\ntest\n-----END OPENSSH PRIVATE KEY-----",
            ];

            $path = $this->github->storeSSHKey($keyData);

            expect($path)
                ->toBeString()
                ->toContain('id_deploy');

            // Clean up
            if (file_exists($path)) {
                unlink($path);
            }
        });
    });

    describe('getStoredSSHKeyPath', function () {
        it('returns null when key does not exist', function () {
            $path = $this->github->getStoredSSHKeyPath();

            expect($path)->toBeNull();
        });

        it('returns path when key exists', function () {
            $testKey = "-----BEGIN OPENSSH PRIVATE KEY-----\ntest\n-----END OPENSSH PRIVATE KEY-----";
            $keyData = ['private_key' => $testKey];

            $this->github->storeSSHKey($keyData);
            $path = $this->github->getStoredSSHKeyPath();

            expect($path)->toContain('id_deploy');

            // Clean up
            if (file_exists($path)) {
                unlink($path);
            }
        });
    });
});

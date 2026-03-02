<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pest Test Configuration
|--------------------------------------------------------------------------
|
| This file is used to configure Pest for testing the Laravel package.
|
*/

use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Test Environment
|--------------------------------------------------------------------------
*/

uses(Arseno25\HostingLaravelDeploy\Tests\TestCase::class)
    ->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Mock Configuration
|--------------------------------------------------------------------------
*/

function mockConfig(): void
{
    config([
        'hosting-deploy.server' => [
            'host' => 'test-server.com',
            'port' => 22,
            'username' => 'testuser',
            'password' => 'testpass',
            'timeout' => 30,
            'ssh_key_path' => null,
        ],
        'hosting-deploy.deployment' => [
            'project_dir' => '/var/www/html',
            'composer_flags' => '--no-dev --optimize-autoloader',
            'run_migrations' => true,
            'run_seeders' => false,
            'clear_cache' => true,
            'optimize' => true,
        ],
        'hosting-deploy.options' => [
            'fresh' => false,
            'link_storage' => true,
            'build_frontend' => true,
        ],
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
}

expect()->extend('toBeValidSSHKey', function () {
    expect($this->value)
        ->toBeString()
        ->toContain('BEGIN')
        ->toContain('PRIVATE KEY');

    return $this;
});

expect()->extend('toBeValidDeployScript', function () {
    expect($this->value)
        ->toBeString()
        ->toContain('cd')
        ->toContain('git pull')
        ->toContain('composer install');

    return $this;
});

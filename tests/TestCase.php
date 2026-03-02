<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Tests;

use Arseno25\HostingLaravelDeploy\HostingDeployServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake HTTP for GitHub API calls
        Http::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HostingDeployServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Setup default configuration for testing
        $app['config']->set('hosting-deploy.server', [
            'host' => 'test-server.com',
            'port' => 22,
            'username' => 'testuser',
            'password' => 'testpass',
            'timeout' => 30,
            'ssh_key_path' => null,
        ]);

        $app['config']->set('hosting-deploy.deployment', [
            'project_dir' => '/var/www/html',
            'composer_flags' => '--no-dev --optimize-autoloader',
            'run_migrations' => true,
            'run_seeders' => false,
            'clear_cache' => true,
            'optimize' => true,
        ]);

        $app['config']->set('hosting-deploy.options', [
            'fresh' => false,
            'link_storage' => true,
            'build_frontend' => true,
        ]);

        $app['config']->set('hosting-deploy.github', [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
            'setup_ssh_keys' => false,
        ]);

        $app['config']->set('hosting-deploy.ssh', [
            'key_type' => 'ed25519',
            'key_bits' => 4096,
        ]);
    }

    protected function defineRoutes($router): void
    {
        // Define any test routes here if needed
    }
}

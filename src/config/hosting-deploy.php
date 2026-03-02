<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your SSH server credentials for deployment.
    | All sensitive data should be stored in your .env file.
    |
    */

    'server' => [

        'host' => env('DEPLOY_HOST', 'localhost'),

        'port' => env('DEPLOY_PORT', 22),

        'username' => env('DEPLOY_USERNAME'),

        'password' => env('DEPLOY_PASSWORD'),

        'timeout' => env('DEPLOY_TIMEOUT', 30),

        'ssh_key_path' => env('DEPLOY_SSH_KEY_PATH'),

    ],

    /*
    |--------------------------------------------------------------------------
    | SSH Key Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SSH key generation and management.
    |
    */

    'ssh' => [

        'key_type' => env('DEPLOY_SSH_KEY_TYPE', 'ed25519'),

        'key_bits' => env('DEPLOY_SSH_KEY_BITS', 4096),

    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | Configure deployment behavior and project directory.
    |
    */

    'deployment' => [

        'project_dir' => env('DEPLOY_PROJECT_DIR'),

        'composer_flags' => env('DEPLOY_COMPOSER_FLAGS', '--no-dev --optimize-autoloader'),

        'run_migrations' => env('DEPLOY_RUN_MIGRATIONS', true),

        'run_seeders' => env('DEPLOY_RUN_SEEDERS', false),

        'clear_cache' => env('DEPLOY_CLEAR_CACHE', true),

        'optimize' => env('DEPLOY_OPTIMIZE', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Options
    |--------------------------------------------------------------------------
    |
    | Additional deployment options.
    |
    */

    'options' => [

        'fresh' => env('DEPLOY_FRESH', false),

        'link_storage' => env('DEPLOY_LINK_STORAGE', true),

        'build_frontend' => env('DEPLOY_BUILD_FRONTEND', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub Configuration
    |--------------------------------------------------------------------------
    |
    | Configure GitHub repository and authentication for deployment.
    | Use a fine-grained Personal Access Token with limited access.
    |
    */

    'github' => [

        'token' => env('DEPLOY_GITHUB_TOKEN'),

        'repo' => env('DEPLOY_REPO'),

        'default_branch' => env('DEPLOY_DEFAULT_BRANCH', 'main'),

        'setup_ssh_keys' => env('DEPLOY_SETUP_SSH_KEYS', false),

    ],

];

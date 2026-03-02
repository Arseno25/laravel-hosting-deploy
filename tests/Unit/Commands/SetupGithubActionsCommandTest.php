<?php

declare(strict_types=1);

use Arseno25\HostingLaravelDeploy\Commands\SetupGithubActionsCommand;

beforeEach(function () {
    config([
        'hosting-deploy.github' => [
            'token' => 'test-token',
            'repo' => 'test/repo',
            'default_branch' => 'main',
        ],
    ]);
});

describe('SetupGithubActionsCommand', function () {
    it('has correct signature', function () {
        $command = new SetupGithubActionsCommand;

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        expect($signatureProperty->getValue($command))->toContain('hosting-deploy:github-actions');
    });

    it('has correct description', function () {
        $command = new SetupGithubActionsCommand;

        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);

        expect($descriptionProperty->getValue($command))->toBe('Generate GitHub Actions workflow file for automated deployment');
    });

    it('stub file exists', function () {
        $stubPath = realpath(__DIR__.'/../../../stubs/github-actions-deploy.stub');

        expect($stubPath)->toBeString()->not->toBeFalse();
    });
});

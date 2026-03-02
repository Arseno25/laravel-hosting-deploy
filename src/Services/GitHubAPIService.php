<?php

declare(strict_types=1);

namespace Arseno25\HostingLaravelDeploy\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GitHubAPIService
{
    protected string $baseUrl = 'https://api.github.com';

    protected ?string $token;

    protected ?string $repo;

    public function __construct()
    {
        $this->token = config('hosting-deploy.github.token');
        $this->repo = config('hosting-deploy.github.repo');
    }

    /**
     * Check if a deploy key already exists for the repository.
     */
    public function deployKeyExists(string $publicKey): bool
    {
        $response = Http::withToken($this->token)
            ->accept('application/vnd.github+json')
            ->get("{$this->baseUrl}/repos/{$this->repo}/keys");

        if ($response->failed()) {
            Log::error('Failed to fetch deploy keys', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $keys = $response->json();

        if ($keys === null || !is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (isset($key['key']) && trim($key['key']) === trim($publicKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a deploy key to the repository.
     */
    public function addDeployKey(string $title, string $publicKey): bool
    {
        $response = Http::withToken($this->token)
            ->accept('application/vnd.github+json')
            ->post("{$this->baseUrl}/repos/{$this->repo}/keys", [
                'title' => $title,
                'key' => $publicKey,
                'read_only' => true,
            ]);

        if ($response->failed()) {
            Log::error('Failed to add deploy key', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return $response->successful();
    }

    /**
     * Encrypt a value using LibSodium for GitHub secrets.
     */
    public function encryptForGitHubSecret(string $value): string
    {
        $key = sodium_crypto_box_keypair();

        $publicKey = sodium_crypto_box_publickey($key);

        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

        $encrypted = sodium_crypto_box(
            $value,
            $nonce,
            $publicKey
        );

        sodium_memzero($value);

        return base64_encode($nonce . $encrypted);
    }

    /**
     * Get the public key for GitHub secrets encryption.
     */
    public function getRepoPublicKey(): ?array
    {
        $response = Http::withToken($this->token)
            ->accept('application/vnd.github+json')
            ->get("{$this->baseUrl}/repos/{$this->repo}/actions/secrets/public-key");

        if ($response->failed()) {
            Log::error('Failed to fetch repo public key', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Create or update a repository secret using GitHub API.
     */
    public function setRepoSecret(string $secretName, string $secretValue): bool
    {
        $publicKeyData = $this->getRepoPublicKey();

        if ($publicKeyData === null) {
            return false;
        }

        $encryptedValue = $this->encryptSecret(
            $secretValue,
            $publicKeyData['key']
        );

        $response = Http::withToken($this->token)
            ->accept('application/vnd.github+json')
            ->put("{$this->baseUrl}/repos/{$this->repo}/actions/secrets/{$secretName}", [
                'encrypted_value' => $encryptedValue,
                'key_id' => $publicKeyData['key_id'],
            ]);

        if ($response->failed()) {
            Log::error('Failed to set repo secret', [
                'secret' => $secretName,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return $response->successful();
    }

    /**
     * Encrypt a secret value using the repository's public key.
     */
    protected function encryptSecret(string $value, string $publicKey): string
    {
        $key = base64_decode($publicKey);

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $box = sodium_crypto_box(
            $value,
            $nonce,
            $key
        );

        sodium_memzero($value);

        return base64_encode($nonce . $box);
    }

    /**
     * Check if a secret exists in the repository.
     */
    public function secretExists(string $secretName): bool
    {
        $response = Http::withToken($this->token)
            ->accept('application/vnd.github+json')
            ->get("{$this->baseUrl}/repos/{$this->repo}/actions/secrets/{$secretName}");

        return $response->successful();
    }

    /**
     * Generate a random SSH key pair.
     */
    public function generateSSHKeyPair(): array
    {
        $keyName = 'deploy_' . Str::random(8) . '_' . time();
        $privateKeyPath = storage_path("app/deploy/{$keyName}");
        $publicKeyPath = $privateKeyPath . '.pub';

        if (!is_dir(dirname($privateKeyPath))) {
            mkdir(dirname($privateKeyPath), 0755, true);
        }

        $keyType = config('hosting-deploy.ssh.key_type', 'ed25519');
        $keyBits = config('hosting-deploy.ssh.key_bits', 4096);

        $keygenCommand = match ($keyType) {
            'ed25519' => "ssh-keygen -t ed25519 -f {$privateKeyPath} -N '' -C {$keyName}",
            default => "ssh-keygen -t rsa -b {$keyBits} -f {$privateKeyPath} -N '' -C {$keyName}",
        };

        exec($keygenCommand . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Failed to generate SSH key pair.');
        }

        if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
            throw new \Exception('SSH key files were not created.');
        }

        $privateKey = file_get_contents($privateKeyPath);
        $publicKey = trim(file_get_contents($publicKeyPath));

        return [
            'name' => $keyName,
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'private_key_path' => $privateKeyPath,
        ];
    }

    /**
     * Store SSH key for deployment.
     */
    public function storeSSHKey(array $keyData): string
    {
        $keyPath = storage_path('app/deploy/id_deploy');

        if (!is_dir(dirname($keyPath))) {
            mkdir(dirname($keyPath), 0755, true);
        }

        file_put_contents($keyPath, $keyData['private_key']);
        chmod($keyPath, 0600);

        return $keyPath;
    }

    /**
     * Get stored SSH key path.
     */
    public function getStoredSSHKeyPath(): ?string
    {
        $keyPath = storage_path('app/deploy/id_deploy');

        if (file_exists($keyPath)) {
            return $keyPath;
        }

        return null;
    }
}

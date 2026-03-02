# Security Policy

## Supported Versions

| Version | Supported          |
|---------|-------------------|
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please **DO NOT** create a public issue.

Instead, please send an email to the package maintainer at:

**Email:** `security@arseno25.com`

Please include:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Suggested fix (if available)

## Response Time

We aim to acknowledge your report within **48 hours** and provide a fix within **7 days**, depending on the severity of the vulnerability.

## Disclosure Policy

Once a vulnerability is fixed:

1. A new version with the fix will be released
2. The security advisory will be published on GitHub
3. Users will be notified to update to the latest version

## Security Best Practices

### For Users

1. **Keep dependencies updated** - Always use the latest version
2. **Use SSH keys** - SSH key authentication is more secure than passwords
3. **Protect your credentials** - Never commit sensitive data to version control
4. **Use environment variables** - Store all sensitive data in `.env` files
5. **Limit GitHub token permissions** - Only grant necessary permissions
6. **Use fine-grained tokens** - Prefer fine-grained tokens over classic tokens
7. **Rotate credentials regularly** - Change SSH keys and tokens periodically

### For Deployments

1. **SSH Key Authentication (Recommended)**
   - Use ED25519 keys (default)
   - Store private keys with `0600` permissions
   - Use read-only deploy keys on GitHub

2. **GitHub Secrets**
   - Secrets are encrypted using LibSodium
   - Never log secret values
   - Use minimal required permissions

3. **Server Security**
   - Keep server software updated
   - Use firewall rules
   - Disable password authentication for SSH
   - Use key-based authentication only

## Security Features

This package includes several security features:

| Feature | Description |
|---------|-------------|
| **ED25519 Keys** | Modern, secure SSH key algorithm |
| **LibSodium Encryption** | End-to-end encryption for GitHub secrets |
| **Read-Only Deploy Keys** | Deploy keys cannot modify repositories |
| **Proper Permissions** | Private keys stored with `0600` permissions |
| **Password Suppression** - Sensitive data hidden from error messages |
| **Connection Timeout** | Prevents hanging connections |

## Vulnerability Scanning

We regularly scan dependencies for known vulnerabilities using:

- Composer security audits
- GitHub Dependabot alerts
- GitHub Advisory Database

## Security Audits

Periodic security audits are conducted to identify potential vulnerabilities. Results are published as security advisories.

## Responsible Disclosure

We appreciate responsible disclosure and will:

- Acknowledge your report promptly
- Work with you to understand the issue
- Provide a timeline for the fix
- Credit you in the security advisory (if desired)

## Threat Model

This package is designed to protect against:

- **Credential exposure** - Secrets are encrypted and never logged
- **Unauthorized access** - Uses secure SSH authentication
- **Man-in-the-middle attacks** - Supports strict host key checking
- **Key compromise** - Supports key rotation and revocation

## Contact

For security-related questions that are not vulnerability reports, please open a GitHub issue with the `security-question` label.

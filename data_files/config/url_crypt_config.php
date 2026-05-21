<?php
/**
 * URL Crypt configuration loader.
 *
 * Reads URL_CRYPT_KEY from the project root .env file (if not already in env),
 * then provides a singleton UrlCrypt instance and global shorthand functions.
 *
 * Include once near the top of any file that needs URL encryption:
 *   require_once __DIR__ . '/url_crypt_config.php';
 *
 * Then use:
 *   $token = encryptURLId(42);
 *   $id    = decryptURLId($token);
 */

require_once __DIR__ . '/UrlCrypt.php';

// ── Load .env (idempotent) ───────────────────────────────────────────────────
if (!getenv('URL_CRYPT_KEY')) {
    $envFile = dirname(__DIR__, 2) . '/.env'; // project root
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if ($name !== '' && !getenv($name)) {
                putenv("{$name}={$value}");
            }
        }
    }
}

// ── Singleton ────────────────────────────────────────────────────────────────
function _urlCryptInstance(): UrlCrypt
{
    static $instance = null;
    if ($instance === null) {
        $instance = UrlCrypt::fromEnv();
    }
    return $instance;
}

// ── Global helper functions ──────────────────────────────────────────────────

/**
 * Encrypt a single integer database ID into a URL-safe token.
 *
 * @param int    $id   The raw integer ID to hide.
 * @param int    $ttl  Seconds until the token expires (0 = never).
 * @param string $ctx  Optional route/context binding to prevent cross-route reuse.
 */
function encryptURLId(int $id, int $ttl = 0, string $ctx = ''): string
{
    return _urlCryptInstance()->encryptId($id, $ttl, $ctx);
}

/**
 * Decrypt a token produced by encryptURLId().
 * Returns the integer ID, or null if invalid/expired/tampered.
 */
function decryptURLId(string $token, string $ctx = ''): ?int
{
    return _urlCryptInstance()->decryptId($token, $ctx);
}

/**
 * Encrypt a plain string value into a URL-safe token.
 */
function encryptURLParameter(string $value, int $ttl = 0, string $ctx = ''): string
{
    return _urlCryptInstance()->encryptString($value, $ttl, $ctx);
}

/**
 * Decrypt a token produced by encryptURLParameter().
 * Returns the string or null if invalid/expired/tampered.
 */
function decryptURLParameter(string $token, string $ctx = ''): ?string
{
    return _urlCryptInstance()->decryptString($token, $ctx);
}

/**
 * Encrypt an associative array into a URL-safe token.
 *
 * @param array  $data Associative array to encrypt.
 * @param int    $ttl  Seconds until expiry (0 = never).
 * @param string $ctx  Optional context binding.
 */
function encryptURLArray(array $data, int $ttl = 0, string $ctx = ''): string
{
    return _urlCryptInstance()->encryptArray($data, $ttl, $ctx);
}

/**
 * Decrypt a token produced by encryptURLArray().
 * Returns the array or null if invalid/expired/tampered.
 */
function decryptURLArray(string $token, string $ctx = ''): ?array
{
    return _urlCryptInstance()->decryptArray($token, $ctx);
}

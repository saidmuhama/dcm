<?php
/**
 * UrlCrypt — AES-256-CBC + HMAC-SHA256 URL parameter encryption.
 *
 * Token format (base64url):
 *   VERSION(1) | IV(16) | CIPHERTEXT(N) | HMAC(32)
 *
 * Key derivation: HKDF-lite — enc key and mac key derived independently
 * from the master via hash_hmac so a compromised one does not expose the other.
 *
 * Payload JSON: { d: <data>, i: <issued_at>, [e: <expires_at>], [c: <context>] }
 *
 * Usage:
 *   $crypt = new UrlCrypt($masterKey);           // or UrlCrypt::fromEnv()
 *   $token = $crypt->encryptId(42);
 *   $id    = $crypt->decryptId($token);          // int|null
 *   $tok   = $crypt->encryptArray(['id'=>42,'tenant'=>3], ttl: 900);
 *   $arr   = $crypt->decryptArray($tok);         // array|null
 */
class UrlCrypt
{
    private const VERSION   = "\x01";
    private const CIPHER    = 'aes-256-cbc';
    private const IV_LEN    = 16;
    private const HMAC_LEN  = 32;
    private const HMAC_ALGO = 'sha256';

    private string $encKey;
    private string $macKey;

    public function __construct(string $masterKeyHex)
    {
        if (strlen($masterKeyHex) < 32) {
            throw new \InvalidArgumentException('Master key must be at least 32 hex characters.');
        }
        $master       = hex2bin($masterKeyHex);
        $this->encKey = hash_hmac(self::HMAC_ALGO, 'enc', $master, true);
        $this->macKey = hash_hmac(self::HMAC_ALGO, 'mac', $master, true);
    }

    /** Load master key from URL_CRYPT_KEY env var (set by url_crypt_config.php). */
    public static function fromEnv(): self
    {
        $key = getenv('URL_CRYPT_KEY');
        if (!$key) {
            throw new \RuntimeException('URL_CRYPT_KEY environment variable not set.');
        }
        return new self($key);
    }

    /** Generate a new random 256-bit master key (hex). */
    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    // ── Public API ───────────────────────────────────────────────

    /**
     * Encrypt a single integer ID.
     * @param int|null $id
     * @param int      $ttl  Seconds until expiry (0 = no expiry)
     * @param string   $ctx  Optional binding context (e.g. route name)
     */
    public function encryptId(int $id, int $ttl = 0, string $ctx = ''): string
    {
        return $this->encrypt(['_id' => $id], $ttl, $ctx);
    }

    /**
     * Decrypt a token produced by encryptId().
     * Returns the integer ID or null on failure.
     */
    public function decryptId(string $token, string $ctx = ''): ?int
    {
        $data = $this->decrypt($token, $ctx);
        if (!is_array($data) || !array_key_exists('_id', $data)) return null;
        $v = $data['_id'];
        return is_int($v) ? $v : (is_numeric($v) ? (int)$v : null);
    }

    /**
     * Encrypt a plain string value.
     */
    public function encryptString(string $value, int $ttl = 0, string $ctx = ''): string
    {
        return $this->encrypt(['_s' => $value], $ttl, $ctx);
    }

    /**
     * Decrypt a token produced by encryptString().
     */
    public function decryptString(string $token, string $ctx = ''): ?string
    {
        $data = $this->decrypt($token, $ctx);
        if (!is_array($data) || !array_key_exists('_s', $data)) return null;
        return is_string($data['_s']) ? $data['_s'] : null;
    }

    /**
     * Encrypt an associative array.
     */
    public function encryptArray(array $payload, int $ttl = 0, string $ctx = ''): string
    {
        return $this->encrypt($payload, $ttl, $ctx);
    }

    /**
     * Decrypt a token produced by encryptArray().
     * Returns the array or null on failure.
     */
    public function decryptArray(string $token, string $ctx = ''): ?array
    {
        $data = $this->decrypt($token, $ctx);
        return is_array($data) ? $data : null;
    }

    // ── Core encrypt / decrypt ───────────────────────────────────

    /**
     * Encrypt any serialisable data.
     * Returns a URL-safe base64 token string.
     */
    public function encrypt(mixed $data, int $ttl = 0, string $ctx = ''): string
    {
        $now     = time();
        $wrapper = ['d' => $data, 'i' => $now];
        if ($ttl > 0) $wrapper['e'] = $now + $ttl;
        if ($ctx !== '') $wrapper['c'] = hash_hmac(self::HMAC_ALGO, $ctx, $this->macKey);

        $plaintext  = json_encode($wrapper, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $iv         = random_bytes(self::IV_LEN);
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->encKey, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        $payload = self::VERSION . $iv . $ciphertext;
        $hmac    = hash_hmac(self::HMAC_ALGO, $payload, $this->macKey, true);

        return self::b64url($payload . $hmac);
    }

    /**
     * Decrypt a token. Returns the original data or null on any failure.
     */
    public function decrypt(string $token, string $ctx = ''): mixed
    {
        $raw = self::b64urlDecode($token);
        if ($raw === null) return null;

        $minLen = 1 + self::IV_LEN + 1 + self::HMAC_LEN; // version + iv + ≥1B cipher + hmac
        if (strlen($raw) < $minLen) return null;

        // Split: everything except last 32 bytes is payload; last 32 = HMAC
        $hmacOffset = strlen($raw) - self::HMAC_LEN;
        $payload    = substr($raw, 0, $hmacOffset);
        $givenHmac  = substr($raw, $hmacOffset);

        // Constant-time MAC verification (encrypt-then-MAC)
        $expectedHmac = hash_hmac(self::HMAC_ALGO, $payload, $this->macKey, true);
        if (!hash_equals($expectedHmac, $givenHmac)) return null;

        // Version check
        if ($payload[0] !== self::VERSION) return null;

        // Extract IV and ciphertext
        $iv         = substr($payload, 1, self::IV_LEN);
        $ciphertext = substr($payload, 1 + self::IV_LEN);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $this->encKey, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) return null;

        $wrapper = json_decode($plaintext, true);
        if (!is_array($wrapper) || !array_key_exists('d', $wrapper)) return null;

        // Expiry check
        if (isset($wrapper['e']) && time() > (int)$wrapper['e']) return null;

        // Context binding check
        if ($ctx !== '') {
            $expectedCtx = hash_hmac(self::HMAC_ALGO, $ctx, $this->macKey);
            if (!isset($wrapper['c']) || !hash_equals($expectedCtx, $wrapper['c'])) return null;
        }

        return $wrapper['d'];
    }

    // ── Encoding helpers ─────────────────────────────────────────

    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64urlDecode(string $token): ?string
    {
        // Restore padding
        $b64 = strtr($token, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) $b64 .= str_repeat('=', 4 - $pad);

        $decoded = base64_decode($b64, true);
        return $decoded !== false ? $decoded : null;
    }
}

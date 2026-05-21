<?php
/**
 * DcmCache — fast file-based cache with optional Redis backend.
 *
 * Usage:
 *   DcmCache::remember('key', 300, fn() => expensiveQuery());
 *   DcmCache::set('key', $val, 60);
 *   DcmCache::get('key');
 *   DcmCache::delete('key');
 *   DcmCache::flush('namespace');
 */
class DcmCache
{
    private static string $dir    = '';
    private static string $secret = 'dcm_cache_hmac_k2026_xJ9p';
    private static ?Redis $redis  = null;
    private static bool   $redisTried = false;

    /* ── Bootstrap ──────────────────────────────────────────── */
    private static function init(): void
    {
        if (self::$dir) return;
        self::$dir = dirname(__DIR__) . '/cache/';
        if (!is_dir(self::$dir)) @mkdir(self::$dir, 0700, true);
        $htaccess = self::$dir . '.htaccess';
        if (!file_exists($htaccess)) file_put_contents($htaccess, "Require all denied\n");
    }

    /* ── Redis (optional, silently falls back to file) ──────── */
    private static function redis(): ?Redis
    {
        if (self::$redisTried) return self::$redis;
        self::$redisTried = true;
        if (!extension_loaded('redis')) return null;
        try {
            $r = new Redis();
            if (@$r->connect('127.0.0.1', 6379, 0.5)) {
                self::$redis = $r;
            }
        } catch (Throwable) {}
        return self::$redis;
    }

    /* ── Key helpers ────────────────────────────────────────── */
    private static function cacheKey(string $key, string $ns): string
    {
        return 'dcm:' . $ns . ':' . $key;
    }

    private static function filePath(string $key, string $ns): string
    {
        self::init();
        $hash = hash_hmac('sha256', $ns . ':' . $key, self::$secret);
        return self::$dir . 'c_' . substr($hash, 0, 40) . '.bin';
    }

    /* ── GET ────────────────────────────────────────────────── */
    public static function get(string $key, string $ns = 'dcm'): mixed
    {
        $r = self::redis();
        if ($r) {
            $raw = $r->get(self::cacheKey($key, $ns));
            if ($raw === false) return null;
            return unserialize($raw);
        }
        // File fallback
        $file = self::filePath($key, $ns);
        if (!file_exists($file)) return null;
        $fh = @fopen($file, 'r');
        if (!$fh) return null;
        flock($fh, LOCK_SH);
        $raw = stream_get_contents($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('v', $data)) return null;
        if ($data['e'] !== 0 && $data['e'] < time()) { @unlink($file); return null; }
        return $data['v'];
    }

    /* ── SET ────────────────────────────────────────────────── */
    public static function set(string $key, mixed $val, int $ttl = 300, string $ns = 'dcm'): bool
    {
        $r = self::redis();
        if ($r) {
            $raw = serialize($val);
            return $ttl > 0 ? (bool)$r->setex(self::cacheKey($key, $ns), $ttl, $raw)
                            : (bool)$r->set(self::cacheKey($key, $ns), $raw);
        }
        // File fallback
        $file = self::filePath($key, $ns);
        $data = ['e' => $ttl > 0 ? time() + $ttl : 0, 'v' => $val];
        $fh = @fopen($file, 'c');
        if (!$fh) return false;
        flock($fh, LOCK_EX);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, serialize($data));
        flock($fh, LOCK_UN);
        fclose($fh);
        return true;
    }

    /* ── DELETE ─────────────────────────────────────────────── */
    public static function delete(string $key, string $ns = 'dcm'): void
    {
        $r = self::redis();
        if ($r) { $r->del(self::cacheKey($key, $ns)); return; }
        @unlink(self::filePath($key, $ns));
    }

    /* ── REMEMBER (get or compute) ──────────────────────────── */
    public static function remember(string $key, int $ttl, callable $fn, string $ns = 'dcm'): mixed
    {
        $val = self::get($key, $ns);
        if ($val !== null) return $val;
        $val = $fn();
        self::set($key, $val, $ttl, $ns);
        return $val;
    }

    /* ── FLUSH namespace (file backend only) ────────────────── */
    public static function flush(string $ns = 'dcm'): void
    {
        $r = self::redis();
        if ($r) {
            // Scan & delete keys matching pattern
            $pattern = self::cacheKey('*', $ns);
            $cursor = null;
            do {
                $keys = $r->scan($cursor, $pattern, 200);
                if ($keys) $r->del($keys);
            } while ($cursor);
            return;
        }
        self::init();
        foreach (glob(self::$dir . 'c_*.bin') as $f) @unlink($f);
    }

    /* ── INVALIDATE a list of keys ──────────────────────────── */
    public static function forget(array $keys, string $ns = 'dcm'): void
    {
        foreach ($keys as $key) self::delete($key, $ns);
    }
}

<?php
/* Pure PHP TOTP — RFC 6238 / RFC 4226  (no external library) */

function totp_random_secret(int $len = 20): string {
    $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $len; $i++) $secret .= $chars[random_int(0, 31)];
    return $secret;
}

function totp_base32_decode(string $secret): string {
    $map    = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
    $secret = strtoupper(preg_replace('/[\s=]+/', '', $secret));
    $bits   = '';
    foreach (str_split($secret) as $c) {
        if (!isset($map[$c])) continue;
        $bits .= str_pad(decbin($map[$c]), 5, '0', STR_PAD_LEFT);
    }
    $bytes = '';
    for ($i = 0; $i + 7 < strlen($bits); $i += 8)
        $bytes .= chr(bindec(substr($bits, $i, 8)));
    return $bytes;
}

function totp_code(string $secret, int $slice = 0): string {
    if (!$slice) $slice = (int) floor(time() / 30);
    $key  = totp_base32_decode($secret);
    $msg  = pack('J', $slice);                          // 8-byte big-endian
    $hmac = hash_hmac('sha1', $msg, $key, true);
    $off  = ord($hmac[19]) & 0x0F;
    $val  = (unpack('N', substr($hmac, $off, 4))[1]) & 0x7FFFFFFF;
    return str_pad($val % 1_000_000, 6, '0', STR_PAD_LEFT);
}

function totp_verify(string $secret, string $code, int $window = 1): bool {
    $code = preg_replace('/\s+/', '', $code);
    if (!preg_match('/^\d{6}$/', $code)) return false;
    $slice = (int) floor(time() / 30);
    for ($i = -$window; $i <= $window; $i++)
        if (hash_equals(totp_code($secret, $slice + $i), $code)) return true;
    return false;
}

function totp_uri(string $secret, string $account, string $issuer = 'DigitalClass'): string {
    return 'otpauth://totp/'
        . rawurlencode($issuer . ':' . $account)
        . '?secret='  . $secret
        . '&issuer='  . rawurlencode($issuer)
        . '&digits=6&period=30&algorithm=SHA1';
}

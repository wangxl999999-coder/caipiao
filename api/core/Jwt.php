<?php
/**
 * JWT 工具类
 */

namespace Core;

class Jwt
{
    private static $secret;
    private static $expire;

    public static function init()
    {
        $config = require __DIR__ . '/../config/app.php';
        self::$secret = $config['jwt']['secret'] ?? 'default_secret';
        self::$expire = $config['jwt']['expire'] ?? 7 * 24 * 3600;
    }

    public static function encode($payload)
    {
        if (!self::$secret) {
            self::init();
        }

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $now = time();
        $payload = array_merge([
            'iss' => 'caipiao_api',
            'aud' => 'caipiao_miniapp',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::$expire,
        ], $payload);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = self::generateSignature($headerEncoded, $payloadEncoded);

        return implode('.', [$headerEncoded, $payloadEncoded, $signature]);
    }

    public static function decode($token)
    {
        if (!self::$secret) {
            self::init();
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signature) = $parts;

        $expectedSignature = self::generateSignature($headerEncoded, $payloadEncoded);
        if (!self::hashEquals($expectedSignature, $signature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public static function validate($token)
    {
        $payload = self::decode($token);
        return $payload !== false;
    }

    public static function getPayload($token)
    {
        return self::decode($token);
    }

    private static function generateSignature($headerEncoded, $payloadEncoded)
    {
        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", self::$secret, true);
        return self::base64UrlEncode($signature);
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $padding = strlen($data) % 4;
        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($data);
    }

    private static function hashEquals($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        $len = min(strlen($a), strlen($b));
        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0 && strlen($a) === strlen($b);
    }
}

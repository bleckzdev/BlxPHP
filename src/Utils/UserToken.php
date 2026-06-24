<?php

namespace src\Utils;

class UserToken {

    public function __construct() {

    }

    public static function generate($length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public static function encode($idUser, $token): string
    {
        $payload = $idUser . ':' . $token;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public static function decode($encodedToken): ?array
    {
        $decoded = base64_decode(strtr($encodedToken, '-_', '+/'));
        if ($decoded === false) {
            return null;
        }
        $parts = explode(':', $decoded, 2);
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            return null;
        }
        return [
            'id' => $parts[0],
            'token' => $parts[1]
        ];
    }

    public static function hash($token): string
    {
        return hash('sha256', $token);
    }
}

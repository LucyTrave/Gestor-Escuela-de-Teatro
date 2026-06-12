<?php

class Csrf {

    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validatePost(): bool {
        $token = $_POST['csrf_token'] ?? '';

        return is_string($token)
            && $token !== ''
            && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }
}

<?php
namespace CodeFlow\Utils;

class AuthCheck {
    public static function verify(): bool {
        $config = require __DIR__ . '/../../config/config.php';
        
        if ($config['security']['auth_type'] === 'cookie') {
            return self::verifyCookie();
        }
        
        return self::verifyIP();
    }
    
    private static function verifyCookie(): bool {
        if (!isset($_SESSION['auth_token'])) {
            return false;
        }
        
        return $_SESSION['auth_token'] === $_COOKIE['auth_token'] ?? '';
    }
    
    private static function verifyIP(): bool {
        if (!isset($_SESSION['auth_ip'])) {
            return false;
        }
        
        return $_SESSION['auth_ip'] === $_SERVER['REMOTE_ADDR'];
    }
} 
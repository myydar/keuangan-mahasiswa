<?php

class Env {
    private static $loaded = false;
    
    public static function load($path = __DIR__ . '/../.env') {
        if (self::$loaded) {
            return;
        }
        
        if (!file_exists($path)) {
            throw new Exception(".env file not found at: $path");
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set in $_ENV
                $_ENV[$key] = $value;
                
                // Also set in $_SERVER for compatibility
                $_SERVER[$key] = $value;
                
                // Set as putenv for even more compatibility
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

// Auto-load .env file
Env::load();
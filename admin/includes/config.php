<?php
// includes/config.php
function loadEnv($path) {
    if(!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Comment මඟහැරීම
        if (strpos(trim($line), '#') === 0) continue;
        
        // පේළිය "=" ලකුණෙන් වෙන් කිරීම
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Environment variable එකක් ලෙස සහ $_ENV ලෙස සෙට් කිරීම
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}
loadEnv(__DIR__ . '/../.env');

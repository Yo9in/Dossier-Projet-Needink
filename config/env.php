<?php
declare(strict_types=1);

function env_load(string $path): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

      
        $value = trim($value, "\"'");

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

function env_get(string $key, ?string $default = null): ?string {
    $val = $_ENV[$key] ?? getenv($key);
    return ($val === false || $val === null || $val === '') ? $default : (string)$val;
}

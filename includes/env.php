<?php

if (!function_exists('loadEnvFile')) {
    /**
     * Carrega um arquivo .env simples sem sobrescrever variáveis já definidas no ambiente.
     */
    function loadEnvFile(string $envFile): void
    {
        static $loadedFiles = [];

        if (isset($loadedFiles[$envFile]) || !is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '' || getenv($name) !== false) {
                continue;
            }

            $length = strlen($value);
            if ($length >= 2) {
                $firstChar = $value[0];
                $lastChar = $value[$length - 1];
                if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        $loadedFiles[$envFile] = true;
    }
}

if (!function_exists('envValue')) {
    function envValue(string $name, $default = null)
    {
        $value = getenv($name);
        return ($value === false ? $default : $value);
    }
}

if (!function_exists('envBool')) {
    function envBool(string $name, bool $default = false): bool
    {
        $value = getenv($name);
        if ($value === false) {
            return $default;
        }

        $normalized = strtolower(trim((string)$value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }
}

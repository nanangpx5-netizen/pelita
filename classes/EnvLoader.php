<?php
/**
 * EnvLoader Class
 * Loads environment variables from .env file
 */

class EnvLoader {
    public static function load(string $path): void {
        if (!file_exists($path)) {
            // Check if we are in production and variables are already set in server
            // If not, maybe throw an exception or just return (assuming server envs are set)
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

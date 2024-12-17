<?php

declare(strict_types=1);

namespace Nitier;

/**
 * Class Environment
 * @package Nitier
 */
use Symfony\Component\Yaml\Yaml;

class Environment
{
    /**
     * The root of the project.
     * @var string|null
     */
    private static ?string $root = null;

    /**
     * @param string|null $path
     */
    public static function setRoot(?string $path = null): void
    {
        self::$root = $path ?: dirname(__DIR__, 4);
    }

    /**
     * @return string
     */
    public static function getRoot(): string
    {
        if (self::$root === null) {
            self::setRoot(__DIR__);
        }
        return self::$root;
    }

    /**
     * @param string|array|null $filePaths
     */
    public static function load(null|string|array $filePaths = null): void
    {
        if ($filePaths === null) {
            $filePaths = self::getRoot() . DIRECTORY_SEPARATOR . '.env';
        }
        $files = is_array($filePaths) ? $filePaths : [$filePaths];

        foreach ($files as $filePath) {
            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new \RuntimeException("File .env not found or unreadable: $filePath");
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2) + [null, null];
                $key = trim($key);
                $value = self::cleanValue($value);

                if (!empty($key)) {
                    if (getenv($key) !== false) {
                        trigger_error("Warning: The key '$key' already exists and will be overwritten.", E_USER_WARNING);
                    }
                    self::set($key, self::castValue((string) $value));
                }
            }
        }
    }

    /**
     * @param string $filePath
     */
    public static function loadYaml(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("YAML file not found or unreadable: $filePath");
        }
        if (!class_exists(Yaml::class)) {
            throw new \RuntimeException("You need to install the 'symfony/yaml' package to use YAML files.");
        }
        $data = Yaml::parseFile($filePath);

        foreach ($data as $key => $value) {
            self::set($key, self::castValue($value));
        }
    }

    /**
     * @param string $filePath
     */
    public static function loadJson(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("JSON file not found or unreadable: $filePath");
        }

        $data = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data as $key => $value) {
            self::set($key, self::castValue($value));
        }
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value === false ? $default : self::castValue($value);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, mixed $value): void
    {
        if (empty($key)) {
            return;
        }

        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            if (is_object($value)) {
                $value = serialize($value);
            } elseif (is_array($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        $variables = [];
        foreach (getenv() as $key => $value) {
            $variables[$key] = self::castValue($value);
        }
        return $variables;
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    private static function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function castValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (strtolower($value) === 'null') {
            return null;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        if (strtolower($value) === 'false') {
            return false;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if ($result = self::jsonParse($value)) {
            return $result;
        }

        if (self::isSerialized($value)) {
            return unserialize($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @return array|bool
     */
    private static function jsonParse(string $value): array|bool
    {
        $result = json_decode($value, true, 512);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    private static function isSerialized(string $value): bool
    {
        return (bool) preg_match('/^O:\d+:"[\w\\\]+":\d+:{.*}$/', $value);
    }
}


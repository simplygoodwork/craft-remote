<?php

namespace simplygoodwork\remote\helpers;

use Craft;
use craft\helpers\App;
use craft\helpers\StringHelper;

class Helpers
{

    public static function env(string $name)
    {
        return $_SERVER[$name] ?? getenv($name);
    }

    public static function parseEnv(string $str = null)
    {
        if (version_compare(Craft::getVersion(), '3.7.29', '>=')) {
            return App::parseEnv($str);
        }

        if ($str === null) {
            return null;
        }

        if (preg_match('/^\$(\w+)$/', $str, $matches)) {
            $value = self::env($matches[1]);
            if ($value !== false) {
                switch (strtolower($value)) {
                    case 'true':
                        return true;
                    case 'false':
                        return false;
                }
                $str = $value;
            }
        }

        if (StringHelper::startsWith($str, '@')) {
            $str = Craft::getAlias($str, false) ?: $str;
        }

        return $str;
    }

    public static function parseBooleanEnv($value)
    {
        if (version_compare(Craft::getVersion(), '3.7.29', '>=')) {
            return App::parseBooleanEnv($value);
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === 0 || $value === 1) {
            return (bool)$value;
        }

        if (!is_string($value)) {
            return null;
        }

        return filter_var(static::parseEnv($value), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

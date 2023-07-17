<?php

namespace core\helpers;

class AppHelper
{
    /**
     * @param string[] ...$array
     * @return string
     */
    public static function arrayToPg(string ...$array): string
    {
        return '{' . implode(',', $array) . '}';
    }

    /**
     * @param string $array
     * @return array
     */
    public static function arrayFromPg(string $array): array
    {
        return explode(',', str_replace('"', "", trim($array, '{}')));
    }
}

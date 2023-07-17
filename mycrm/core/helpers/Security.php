<?php

namespace core\helpers;

use Yii;

/**
 * @package common\modules\users\helpers
 */
class Security
{
    /**
     * Generate a random key with time suffix.
     * @return string Random key
     */
    public static function generateExpiringRandomString()
    {
        return Yii::$app->getSecurity()->generateRandomString() . '_' . time();
    }

    /**
     * Check if token is not expired.
     *
     * @param string $token Token that must be validated
     * @param integer $duration Time during token is valid
     * @return boolean true if token is not expired
     */
    public static function isValidToken($token, $duration)
    {
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return ($timestamp + $duration > time());
    }

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int $length How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}

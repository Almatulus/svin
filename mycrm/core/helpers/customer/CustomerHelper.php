<?php

namespace core\helpers\customer;

class CustomerHelper
{
    const PHONE_VALIDATE_PATTERN = '/^\+[0-9]{0,3} [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}$/i';

    /**
     * @param $phone
     * @return null|string
     */
    public static function getPhone($phone) {

        if (preg_match('/^\+(\d{1}) (\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $phone, $matches)
            || preg_match('/^\+(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)
            || preg_match('/^(\d{1}) (\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $phone, $matches)
            || preg_match('/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)
        ) {

            $phone = '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];
            return $phone;
        }

        if (preg_match('/^(\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $phone, $matches)
            || preg_match('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)
        ) {

            $phone = '+' . "7" . ' ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4];
            return $phone;
        }

        return null;
    }
}
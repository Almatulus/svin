<?php

namespace core\tests;

use Codeception\Module;
use Codeception\Util\JsonType;

class JsonTypesHelper extends Module
{
    /**
     * @inheritDoc
     */
    public function _initialize()
    {
        JsonType::addCustomFilter('date', function ($value) {
            return preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})$/',
                $value
            );
        });
        JsonType::addCustomFilter('datetime', function ($value) {
            return preg_match(
                '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                $value
            );
        });
        JsonType::addCustomFilter('time', function ($value) {
            return preg_match(
                '/^\d{2}:\d{2}:\d{2}$/',
                $value
            );
        });
    }
}

<?php

namespace core\services\dto;

/**
 * @property string $name
 * @property string $surname
 * @property string $patronymic
 */
class PersonData
{
    public $name;
    public $surname;
    public $patronymic;

    public function __construct($name, $surname, $patronymic)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->patronymic = $patronymic;
    }
}
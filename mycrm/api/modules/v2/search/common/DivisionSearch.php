<?php

namespace api\modules\v2\search\common;

use core\models\division\Division;

class DivisionSearch extends Division
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'address',
            'city_name'    => function () {
                return $this->city->name;
            },
            'country_name' => function () {
                return $this->city->country->name;
            },
            'name',
            'working_start',
            'working_finish'
        ];
    }

    public function extraFields()
    {
        return [];
    }
}
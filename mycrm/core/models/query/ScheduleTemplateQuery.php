<?php

namespace core\models\query;

use core\models\ScheduleTemplate;

/**
 * This is the ActiveQuery class for [[\core\models\ScheduleTemplate]].
 *
 * @see \core\models\ScheduleTemplate
 */
class ScheduleTemplateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\ScheduleTemplate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\ScheduleTemplate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int $division_id
     * @return $this
     */
    public function byDivision(int $division_id)
    {
        return $this->andWhere([ScheduleTemplate::tableName() . ".division_id" => $division_id]);
    }

    /**
     * @param int $staff_id
     * @return $this
     */
    public function byStaff(int $staff_id)
    {
        return $this->andWhere([ScheduleTemplate::tableName() . ".staff_id" => $staff_id]);
    }
}

<?php

namespace core\repositories;

use core\models\ScheduleTemplate;
use core\models\ScheduleTemplateInterval;
use core\repositories\exceptions\NotFoundException;

class ScheduleTemplateRepository extends BaseRepository
{
    /**
     * @param int $staff_id
     * @param int $division_id
     * @return ScheduleTemplate
     */
    public function findByStaffAndDivision(int $staff_id, int $division_id)
    {
        $model = ScheduleTemplate::findOne(['staff_id' => $staff_id, 'division_id' => $division_id]);
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $template_id
     * @return int
     */
    public function clearIntervals(int $template_id)
    {
        return ScheduleTemplateInterval::deleteAll(['schedule_template_id' => $template_id]);
    }
}
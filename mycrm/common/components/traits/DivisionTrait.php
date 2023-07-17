<?php

namespace common\components\traits;

trait DivisionTrait {

    /**
     * Filter by permitted divisions
     * @return self
     */
    public function permitted() {
        $divisions = \Yii::$app->user->identity->permittedDivisions;
        if ($divisions) {
            return $this->andWhere([$this->getDivisionAttribute() => $divisions]);
        }
        return $this;
    }

    abstract public function getDivisionAttribute();
}
<?php

namespace api\modules\v2\search\common;

use core\models\company\query\CompanyQuery;
use core\models\Staff;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class StaffSearch extends Staff
{
    public $division_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['division_id', 'integer']
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function search($params)
    {
        $query = self::find()->enabled()->joinWith([
            'divisions.company' => function (CompanyQuery $query) {
                return $query->enabledIntegration();
            }
        ], false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->division($this->division_id);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'surname',
            'birth_date'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'divisions'
        ];
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [];
    }

    /**
     * @return $this
     */
    public function getDivisions()
    {
        return $this->hasMany(DivisionSearch::class, ['id' => 'division_id'])
            ->viaTable('{{%staff_division_map}}', ['staff_id' => 'id']);
    }
}
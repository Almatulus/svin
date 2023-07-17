<?php

namespace api\modules\v2\search\common;

use core\models\company\query\CompanyQuery;
use core\models\division\DivisionService;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class ServiceSearch extends DivisionService
{
    public $staff_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['staff_id', 'required'],
            ['staff_id', 'integer']
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function search($params)
    {
        $query = self::find()->deleted(false)->joinWith([
            'staffs.divisions.company' => function (CompanyQuery $query) {
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

        $query->andWhere(['{{%staffs}}.id' => $this->staff_id]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'duration' => 'average_time',
            'name'     => 'service_name',
            'price'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}


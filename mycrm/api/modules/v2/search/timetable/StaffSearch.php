<?php

namespace api\modules\v2\search\timetable;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\Staff;
use yii\web\BadRequestHttpException;

class StaffSearch extends Model
{
    public $staffs;
    public $division_id;
    public $position_id;
    public $date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'division_id'], 'required'],
            [['division_id', 'position_id'], 'integer'],
            ['staffs', 'each', 'rule' => ['integer']],
            [['date'], 'datetime', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function search($params)
    {
        $query = Staff::find()->valid();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $query->joinWith(['companyPositions', 'divisions'], false);
        $query->andFilterWhere(['{{%staffs}}.id' => $this->staffs]);
        $query->andFilterWhere(['{{%divisions}}.id' => $this->division_id]);
        $query->andFilterWhere(['{{%company_positions}}.id' => $this->position_id]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

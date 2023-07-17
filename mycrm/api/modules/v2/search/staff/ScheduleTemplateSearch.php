<?php

namespace api\modules\v2\search\staff;

use core\models\ScheduleTemplate;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class ScheduleTemplateSearch extends Model
{
    public $staff_id;
    public $division_id;

    public function rules()
    {
        return [
            [['division_id', 'staff_id'], 'integer']
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
        $query = ScheduleTemplate::find()->joinWith('staff.divisions')->andWhere([
            '{{%divisions}}.company_id' => \Yii::$app->user->identity->company_id
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $query->andFilterWhere([
            '{{%schedule_templates}}.division_id' => $this->division_id,
            '{{%schedule_templates}}.staff_id'    => $this->staff_id,
        ]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
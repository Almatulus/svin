<?php

namespace api\modules\v2\search\staff;

use core\models\StaffSchedule;
use core\models\division\DivisionService;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

/**
 * @property integer $id
 * @property string  $start_time
 * @property string  $finish_time
 * @property integer $staff_id
 * @property integer $division_id
 */
class ScheduleSearch extends StaffSchedule
{
    public $start_time;
    public $finish_time;
    public $division_id;
    public $staff_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['division_id', 'staff_id', 'start_time', 'finish_time'],
                'required',
            ],
            [['division_id', 'staff_id'], 'integer'],
            [
                ['start_time', 'finish_time'],
                'datetime',
                'format' => 'php:Y-m-d H:i:s',
            ],
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
        $query = StaffSchedule::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'staff_id' => SORT_ASC,
                    'division_id' => SORT_ASC,
                    'start_at' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $query->andFilterWhere(['>=', 'start_at', $this->start_time]);
        $query->andFilterWhere(['<=', 'end_at', $this->finish_time]);

        $query->andFilterWhere([
            'division_id' => $this->division_id,
            'staff_id'    => $this->staff_id,
        ]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

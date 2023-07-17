<?php

namespace api\modules\v2\search\user;

use core\models\Staff;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class StaffSearch extends Model
{
    public $id;
    public $staff;
    public $division_id;
    public $status;
    public $term;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'id'], 'integer', 'min' => 1],
            ['staff', 'safe'],
            ['status', 'integer'],
            ['term', 'string']
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
        $query = Staff::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => ['defaultOrder' => ['name' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $query->company()
            ->enabled()
            ->permitted()
            ->andWhere(['{{%staffs}}.has_calendar' => 1]);

        $query->andFilterWhere([
            'id'                 => $this->id,
            '{{%staffs}}.status' => $this->status
        ]);

        $query->andFilterWhere(['{{%staffs}}.id' => $this->staff])
              ->division($this->division_id);

        $query->andFilterWhere([
            'OR',
            ['ILIKE', '{{%staffs}}.name', $this->term],
            ['ILIKE', '{{%staffs}}.surname', $this->term],
            ['LIKE', '{{%staffs}}.phone', $this->term]
        ]);

        /* @var Staff $staff */
        $staff = \Yii::$app->user->identity->staff;
        if ($staff && $staff->see_own_orders) {
            $query->andWhere(['{{%staffs}}.id' => $staff->id]);
        }

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

<?php

namespace api\modules\v2\search\staff;

use core\models\Staff;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class StaffSearch extends Staff
{
    public $division_service_id = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'division_service_id'], 'integer'],
            [['name', 'description'], 'safe'],
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
        $query = Staff::find()->enabled()->timetableVisible();
        if (!\Yii::$app->user->isGuest) {
            $query->permitted();
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        if ($this->division_service_id !== null) {
            $query->joinWith("divisionServices");
            $query->andWhere(['{{%division_services}}.id' => $this->division_service_id]);
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

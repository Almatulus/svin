<?php

namespace api\modules\v2\search\division;

use core\models\Payment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DivisionPaymentSearch represents the model behind the search form about `core\models\Payment`.
 */
class DivisionPaymentSearch extends Model
{
    public $division_id;
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['division_id', 'integer'],
            [['name'], 'string'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Payment::find()
            ->innerJoin('{{%division_payments}}', '{{%division_payments}}.payment_id = {{%payments}}.id')
            ->andWhere(['{{%division_payments}}.division_id' => \Yii::$app->user->identity->getPermittedDivisions()])
            ->andWhere(['{{%payments}}.status' => Payment::STATUS_ENABLED]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
//            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['division_id' => $this->division_id]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return "";
    }
}

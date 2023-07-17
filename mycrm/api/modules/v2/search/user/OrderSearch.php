<?php

namespace api\modules\v2\search\user;

use core\models\order\Order;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class OrderSearch extends Model
{
    public $date;
    public $staff_id;
    public $division_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'staff_id', 'division_id'], 'required'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
            [['division_id'], 'integer', 'min' => 1],
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
        $query = Order::find()->visible()->permitted();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => ['defaultOrder' => ['datetime' => SORT_DESC]]
        ]);

        $this->load($params);

        if ($this->validate()) {
            $query->staff($this->staff_id)
                ->startFrom(new \DateTime($this->date))
                ->to(new \DateTime($this->date))
                ->andWhere(['{{%orders}}.division_id' => $this->division_id]);

            $query->joinWith([
                'orderServices.divisionService',
                'companyCustomer.customer',
                'staff',
            ]);
        }

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

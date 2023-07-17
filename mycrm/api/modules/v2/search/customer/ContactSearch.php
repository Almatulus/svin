<?php

namespace api\modules\v2\search\customer;

use core\models\customer\Customer;
use core\models\customer\CustomerContact;
use yii\data\ActiveDataProvider;

class ContactSearch extends CustomerContact
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['customer_id', 'required'],
            ['customer_id', 'integer'],
            ['customer_id', 'exist', 'targetClass' => Customer::class, 'targetAttribute' => 'id']
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
        $query = self::find()->company();

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->orFilterWhere([
            '{{%customer_contacts}}.contact_id'  => $this->customer_id,
            '{{%customer_contacts}}.customer_id' => $this->customer_id
        ]);

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}
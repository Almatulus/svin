<?php

namespace frontend\modules\admin\search;

use core\models\order\Order;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `core\models\order\Order`.
 *
 * @property string  $start
 * @property string  $end
 * @property integer $company_id
 */
class OrderSearch extends Order
{
    public $start;
    public $end;
    public $company_id;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'type', 'company_id'], 'integer'],
            ['number', 'string'],
            [['start', 'end'], 'datetime', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'company_id' => Yii::t('app', 'Company ID'),
            'start'      => Yii::t('app', 'From date'),
            'end'        => Yii::t('app', 'To date'),
        ]);
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
        $query = self::find();
        $query->joinWith('companyCustomer');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'datetime' => SORT_DESC,
                    'status'   => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            '{{%orders}}.status' => $this->status,
            '{{%orders}}.type'   => $this->type,
        ]);

        $query->andFilterWhere(['like', '{{%orders}}.number', $this->number]);
        $query->andFilterWhere(['{{%company_customers}}.company_id' => $this->company_id]);
        $query->andFilterWhere(['>=', '{{%orders}}.datetime', $this->start]);
        $query->andFilterWhere(['<=', '{{%orders}}.datetime', $this->end]);

        return $dataProvider;
    }
}

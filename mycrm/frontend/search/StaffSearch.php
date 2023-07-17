<?php

namespace frontend\search;

use core\models\company\query\CompanyPositionQuery;
use core\models\Staff;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * StaffSearch represents the model behind the search form about `core\models\Staff`.
 *
 * @property integer $division_id
 */
class StaffSearch extends Staff
{
    public $term;
    public $division_id;
    public $company_position_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'image_id', 'document_scan_id', 'division_id', 'company_position_id'], 'integer'],
            [['name', 'description', 'birth_date', 'term'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'division_id'      => \Yii::t('app', 'Division'),
            'company_position' => \Yii::t('app', 'Company Position'),
            'term'             => \Yii::t('app', 'Term')
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @param bool $disabled
     * @return ActiveDataProvider
     */
    public function search($params, $disabled = false)
    {
        $query = Staff::find()->company()->permitted();

        $disabled ? $query->disabled() : $query->enabled();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'name',
                    'surname' => [
                        'asc'  => [new Expression('{{%staffs}}.surname ASC NULLS LAST')],
                        'desc' => [new Expression('{{%staffs}}.surname DESC NULLS LAST')],
                    ],
                ],
                'defaultOrder' => [
                    'surname' => SORT_ASC,
                    'name'    => SORT_ASC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'                                  => $this->id,
            'crm_staffs.status'                   => $this->status,
            'birth_date'                          => $this->birth_date,
            'image_id'                            => $this->image_id,
            'document_scan_id'                    => $this->document_scan_id,
            '{{%staff_division_map}}.division_id' => $this->division_id,
        ]);

        if (!empty($this->company_position_id)) {
            $query->joinWith([
                'companyPositions' => function (CompanyPositionQuery $query) {
                    return $query->position($this->company_position_id);
                }
            ]);
        }

        $query->andFilterWhere([
            "OR",
            ['~*', '{{%staffs}}.name', $this->term],
            ['~*', '{{%staffs}}.surname', $this->term],
        ]);
        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}

<?php

namespace frontend\modules\admin\search;

use core\models\company\query\CompanyPositionQuery;
use core\models\division\Division;
use core\models\Staff;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StaffSearch represents the model behind the search form about `core\models\Staff`.
 */
class StaffSearch extends Model
{
    public $onlyActive;
    public $usesMobileApp;
    public $division_id;
    public $id;
    public $status;
    public $image_id;
    public $document_scan_id;
    public $company_position_id;
    public $name;
    public $description;
    public $birth_date;
    public $service_category_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'image_id', 'document_scan_id', 'company_position_id', 'division_id', 'service_category_id'], 'integer'],
            [['name', 'description', 'birth_date'], 'safe'],

            ['onlyActive', 'boolean'],
            ['usesMobileApp', 'default', 'value' => null],
            ['usesMobileApp', 'integer'],
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
        $labels = parent::attributeLabels();
        $labels['division_id'] = Yii::t('app', 'Division');
        $labels['onlyActive'] = "Только активные компании";
        $labels['usesMobileApp'] = "Мобильное приложение";
        return $labels;
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
        $query = Staff::find()->enabled()->joinWith('divisions')->distinct();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $divisionIds = null;
        if ($this->onlyActive) {
            $divisionIds = Division::find()->select('{{%divisions}}.id')->enabled()->active(false)->column();
        }

        if ($this->usesMobileApp) {
            $query->joinWith('user', false)->andWhere('device_key IS NOT NULL');
        }

        if ($this->usesMobileApp !== null && !$this->usesMobileApp) {
            $query->joinWith('user', false)->andWhere('device_key IS NULL');
        }

        $query->andFilterWhere([
            'id'                                  => $this->id,
            'crm_staffs.status'                   => $this->status,
            'birth_date'                          => $this->birth_date,
            'image_id'                            => $this->image_id,
            'document_scan_id'                    => $this->document_scan_id,
            '{{%staff_division_map}}.division_id' => $divisionIds,
            '{{%divisions}}.category_id'          => $this->service_category_id,
        ]);
        $query->joinWith(['companyPositions' => function(CompanyPositionQuery $query) {
            return $query->position($this->company_position_id);
        }]);

        $query->andFilterWhere(['{{%staff_division_map}}.division_id' => $this->division_id]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }

}

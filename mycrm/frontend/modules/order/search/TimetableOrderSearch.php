<?php

namespace frontend\modules\order\search;

use core\helpers\TimetableHelper;
use core\models\company\query\CompanyPositionQuery;
use core\models\customer\query\CompanyCustomerQuery;
use core\models\query\StaffQuery;
use core\models\Staff;
use core\models\division\Division;
use core\models\order\Order;
use yii\data\ActiveDataProvider;

/**
 * @property string $start
 * @property string $end
 * @property array $staffs
 * @property string $viewName
 * @property integer $company_id
 * @property integer $position_id
 */
class TimetableOrderSearch extends Order
{
    public $start;
    public $end;
    public $staffs;
    public $viewName;
    public $company_id;
    public $position_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start', 'end'], 'required'],
            [['start', 'end'], 'datetime', 'format' => 'php:Y-m-d'],

            [['division_id', 'position_id'], 'integer'],

            [['viewName'], 'in', 'range' => TimetableHelper::getViews()],
            [['viewName'], 'default', 'value' => TimetableHelper::VIEW_DAY],

            ['staffs', 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find()->permitted()->visible();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => ['defaultOrder' => ['datetime' => SORT_DESC]]
        ]);

        $query->joinWith([
            'orderServices',
            'companyCustomer' => function(CompanyCustomerQuery $query) {
                $query->company($this->company_id);
            },
            'staff' => function(StaffQuery $query) {
                $query->joinWith(['companyPositions' => function(CompanyPositionQuery $query) {
                    $query->position($this->position_id);
                }]);
                $query->joinWith(['divisions']);
                return $query;
            }
        ]);
        $query->staff($this->staffs);

        $startDate = new \DateTime($this->start);
        $endDate = new \DateTime($this->end);
        $endDate->format('-1 day');

        $query->startFrom($startDate);
        $query->to($endDate);
        
        $query->andFilterWhere(['{{%orders}}.division_id' => $this->division_id]); // TODO "staff_division_map" or "division_id" ?

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

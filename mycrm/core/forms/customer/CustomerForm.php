<?php

namespace core\forms\customer;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use Yii;

class CustomerForm extends \yii\base\Model {

    public $category;
    public $division;
    public $numberOfDays = 30;
    public $service;
    public $service_category;
    public $staff;

    private $_orders = [];

    /**
     * @return array
     */
    public function rules() {
        return [
            ['numberOfDays', 'required'],
            ['numberOfDays', 'integer', 'min' => 1],
            [['category', 'division', 'service',
                'service_category', 'staff'], 'integer']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [
            'category' => Yii::t('app', 'Category'),
            'numberOfDays' => Yii::t('app', 'Days from last visit'),
            'division' => Yii::t('app', 'Division ID'),
            'service' => Yii::t('app', 'Service'),
            'service_category' => Yii::t('app', 'Category ID'),
            'staff' => Yii::t('app', 'Staff ID')
        ];
    }

    /**
     * @return $this
     */
    public function getQuery() {

        $date = date("Y-m-d", strtotime("-{$this->numberOfDays} days"));

        $subQuery = Order::find()
                ->select(['company_customer_id', 'MAX(datetime) as max_date'])
                ->where(['crm_orders.status' => OrderConstants::STATUS_FINISHED])
                ->company(false)
                ->groupBy('company_customer_id');

        if ($this->service_category || $this->service) {
            $subQuery->joinWith('orderServices.divisionService.categories', false)
                ->andFilterWhere(['crm_service_categories.id' => $this->service_category])
                ->andFilterWhere(['crm_order_services.division_service_id' => $this->service]);
        }

        if ($this->division) {
            $subQuery->joinWith('staff.divisions', false)
                ->andFilterWhere(['{{%divisions}}.id' => $this->division]);
        }

        if ($this->staff) {
            $subQuery->andFilterWhere(['{{%orders}}.staff_id' => $this->staff]);
        }

        $query = CompanyCustomer::find()
            ->innerJoin(['ord' => $subQuery], 'crm_company_customers.id = ord.company_customer_id')
            ->joinWith(['customer'], true)
            ->where([
                'AND',
                ['<=', 'max_date', $date]
            ])
            ->orderBy('max_date DESC');

        if ($this->category) {
            $query->joinWith('categories', false)
                ->andFilterWhere(['crm_customer_categories.id' => $this->category]);
        }

        return $query;
    }

    /**
     * @param $company_customer_id
     * @return mixed
     */
    public function getLastOrder($company_customer_id) {
        if (!isset($this->_orders[$company_customer_id])) {
            $query = Order::find()
                ->companyCustomerID($company_customer_id)
                ->status(OrderConstants::STATUS_FINISHED)
                ->orderDatetime(SORT_DESC);

            if ($this->service_category || $this->service) {
                $query->joinWith('orderServices.divisionService.categories', false)
                    ->andFilterWhere(['crm_service_categories.id' => $this->service_category])
                    ->andFilterWhere(['crm_order_services.division_service_id' => $this->service]);
            }

            $query->andFilterWhere(['{{%orders}}.division_id' => $this->division]);

            if ($this->staff) {
                $query->andFilterWhere(['staff_id' => $this->staff]);
            }

            $this->_orders[$company_customer_id] = $query->one();
        }

        return $this->_orders[$company_customer_id];
    }
}
<?php

namespace core\forms\customer;

use core\models\customer\CompanyCustomer;
use core\models\customer\query\CompanyCustomerQuery;
use yii\base\Model;

class MergeForm extends Model
{
    public $customer_ids;
    private $primary_customer_id;

    /**
     * MergeForm constructor.
     * @param int $id
     * @param array $config
     */
    public function __construct(int $id, array $config = [])
    {
        $this->primary_customer_id = $id;

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['customer_ids', 'required'],
            ['customer_ids', 'each', 'rule' => ['integer']],
            [
                'customer_ids',
                'each',
                'rule' => [
                    'exist',
                    'targetClass'     => CompanyCustomer::class,
                    'targetAttribute' => 'id',
                    'filter'          => function (CompanyCustomerQuery $query) {
                        return $query->active(true)->company();
                    }
                ]
            ],
            [
                'customer_ids',
                'filter',
                'filter'      => function (array $data) {
                    return array_filter($data, function (int $customer_id) {
                        return $customer_id != $this->primary_customer_id;
                    });
                },
                'skipOnError' => true
            ]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return ['customer_ids' => \Yii::t('app', 'Customers')];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
<?php

namespace core\forms\finance;

use core\models\order\OrderService;
use core\models\order\query\OrderQuery;
use core\services\dto\SalaryServiceData;
use yii\base\Model;

/**
 * Class SalaryCheckoutForm
 * @package core\forms\finance
 *
 * @property string $payment_date
 */

class SalaryCheckoutForm extends SalaryForm
{
    public $salary;
    public $services;
    public $payment_date;

    public $ignore_warnings;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->payment_date = date('Y-m-d');
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['salary', 'required'],
            ['salary', 'integer', 'min' => 1],

            ['payment_date', 'required'],
            ['payment_date', 'date', 'format' => 'yyyy-MM-dd'],

            ['ignore_warnings', 'default', 'value' => false],
            ['ignore_warnings', 'boolean'],

            ['services', 'default', 'value' => []],
            ['services', 'validateServices'],
        ]);
    }

    /**
     * @param $attribute
     */
    public function validateServices($attribute)
    {
        foreach ($this->{$attribute} as $order_service_id => $serviceData) {
            $form = new SalaryServiceForm($this->staff_id, $this->division_id, $this->payment_date,
                $this->ignore_warnings, [
                'order_service_id' => $order_service_id,
                'percent'          => $serviceData['percent'] ?? null,
                'sum'              => $serviceData['sum'] ?? null
            ]);

            if (!$form->validate()) {
                foreach ($form->firstErrors as $attributeName => $errorMessage) {
                    if ($attributeName == "services") {
                        $this->addError("$attributeName", $errorMessage);
                    } else {
                        $this->addError("{$attribute}[$order_service_id][$attributeName]", $errorMessage);
                    }
                }
            }
        }
    }

    /**
     * Converts array of values of primitive type to array of objects
     * Should be called carefully. Model has to be valid.
     * @return array|SalaryServiceData
     */
    public function getFilteredServices()
    {
        $services = [];
        foreach ($this->services as $order_service_id => $serviceData) {
            $services[] = new SalaryServiceData($order_service_id, $serviceData['percent'], $serviceData['sum']);
        }
        return $services;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'salary' => \Yii::t('app', 'Salary'),
            'payment_date' => \Yii::t('app', 'Payment Date')
        ]);
    }
}

class SalaryServiceForm extends Model
{
    public $order_service_id;
    public $percent;
    public $sum;

    private $staff_id;
    private $division_id;
    private $payment_date;
    private $ignore_warnings;

    /**
     * SalaryServiceForm constructor.
     * @param int $staff_id
     * @param int $division_id
     * @param string $payment_date
     * @param bool $ignore_warnings
     * @param array $config
     */
    public function __construct(
        int $staff_id,
        int $division_id,
        string $payment_date,
        bool $ignore_warnings,
        array $config = []
    )
    {
        parent::__construct($config);

        $this->staff_id = $staff_id;
        $this->division_id = $division_id;
        $this->payment_date = $payment_date;
        $this->ignore_warnings = $ignore_warnings;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['order_service_id', 'required'],
            ['order_service_id', 'integer'],
            ['order_service_id', 'validateService', 'skipOnError' => true],

            ['percent', 'required'],
            ['percent', 'integer', 'min' => 0],

            ['sum', 'required'],
            ['sum', 'integer', 'min' => 0],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateService($attribute)
    {
        $id = $this->{$attribute};

        /** @var OrderService $orderService */
        $orderService = OrderService::find()->joinWith([
            'order' => function (OrderQuery $query) {
                $query->finished()
                    ->staff($this->staff_id)
                    ->division($this->division_id)
                    ->unpaid();
            }
        ], false)->andWhere(['{{%order_services}}.deleted_time' => null])
            ->andWhere(['{{%order_services}}.id' => $id])
            ->one();

        if (!$orderService) {
            $this->addError($attribute, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->getAttributeLabel($attribute)
            ]));
            return;
        }

        if (!$this->ignore_warnings
            && (new \DateTime($orderService->order->datetime))->setTime(0, 0,
                0) > (new \DateTime($this->payment_date))) {
            $this->addError("services",
                "У одной или нескольких услуг дата записи меньше чем дата оплаты. Для игнорирования данной ошибки," .
                " нажмите \"Игнорировать предупреждения\"");
        }

    }
}
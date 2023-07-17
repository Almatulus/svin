<?php

namespace core\forms\finance;

use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyContractor;
use core\models\finance\CompanyCostItem;
use core\models\Payment;
use core\models\Staff;
use core\models\user\User;
use Yii;
use yii\base\Model;


class CashflowForm extends Model
{
    protected $company_id;
    protected $user_id;

    public $date;
    public $cash_id;
    public $comment;
    public $contractor_id;
    public $cost_item_id;
    public $customer_id;
    public $division_id;
    public $receiver_mode;
    public $staff_id;
    public $value;

    public $payments;

    /**
     * CashflowForm constructor.
     * @param int $user_id
     * @param array $config
     */
    public function __construct(int $user_id, array $config = [])
    {
        $this->user_id = $user_id;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->date = $this->date ? (new \DateTime($this->date))->format('Y-m-d H:i') : date("Y-m-d H:i");

        $user = User::findOne($this->user_id);

        if (!$user) {
            throw new \InvalidArgumentException("Invalid user");
        }

        $this->company_id = $user->company_id;

        foreach (Division::getAllPayments($user->getPermittedDivisions()) as $payment_id => $name) {
            $this->payments[$payment_id] = ['payment_id' => $payment_id];
        }
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['receiver_mode', 'default', 'value' => CompanyCashflow::RECEIVER_CONTRACTOR],

            [['date', 'cost_item_id', 'cash_id', 'division_id', 'value'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d H:i'],
            [
                [
                    'cash_id',
                    'contractor_id',
                    'cost_item_id',
                    'customer_id',
                    'division_id',
                    'receiver_mode',
                    'staff_id',
                    'value',
                ],
                'integer'
            ],

            [['comment'], 'string'],

            ['payments', 'required'],
            ['payments', 'validatePayments'],

            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
            [
                ['cash_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCash::className(),
                'targetAttribute' => ['cash_id' => 'id']
            ],
            [
                ['contractor_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyContractor::className(),
                'targetAttribute' => ['contractor_id' => 'id']
            ],
            [
                ['cost_item_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCostItem::className(),
                'targetAttribute' => ['cost_item_id' => 'id']
            ],
            [
                ['customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::className(),
                'targetAttribute' => ['customer_id' => 'id']
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],

            [
                'payments',
                'filter',
                'filter' => function (array $payments) {
                    return array_filter($payments, function (array $paymentData) {
                        return $paymentData['value'] > 0;
                    });
                },
                'when'   => function (self $model) {
                    return !$model->hasErrors();
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date'          => Yii::t('app', 'Date'),
            'cash_id'       => Yii::t('app', 'Cash'),
            'comment'       => Yii::t('app', 'Comment'),
            'contractor_id' => Yii::t('app', 'Contractor'),
            'cost_item_id'  => Yii::t('app', 'Cost Item'),
            'customer_id'   => Yii::t('app', 'Customer'),
            'division_id'   => Yii::t('app', 'Division'),
            'staff_id'      => Yii::t('app', 'Staff'),
            'receiver_mode' => Yii::t('app', 'Receiver Mode'),
            'value'         => Yii::t('app', 'Value Cashflow'),
            'payments'      => Yii::t('app', 'Payments'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'CompanyCashflow';
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @return null
     */
    public function getCashflow()
    {
        return null;
    }

    /**
     * @param $attribute
     */
    public function validatePayments($attribute)
    {
        $total = 0;
        foreach ($this->{$attribute} as $payment_id => $data) {
            $form = new CashflowPaymentForm([
                'payment_id' => $data['payment_id'] ?? null,
                'value'      => $data['value'] ?? null
            ]);

            if (!$form->validate()) {
                foreach ($form->firstErrors as $attributeName => $error) {
                    $this->addError("{$attribute}[{$payment_id}][$attributeName]", $error);
                }
            }

            $total += $data['value'] ?? 0;
        }

        if ($this->value != $total) {
            $this->addError($attribute, "Сумма способов оплаты должна совпадать с суммой.");
        }
    }
}

class CashflowPaymentForm extends Model
{
    public $payment_id;
    public $value;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['payment_id', 'required'],
            ['payment_id', 'integer'],
            ['payment_id', 'exist', 'targetClass' => Payment::class, 'targetAttribute' => 'id'],

            ['value', 'required'],
            ['value', 'integer', 'min' => 0]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'payment_id' => Yii::t('app', 'Payment'),
            'value'      => Yii::t('app', 'Sum'),
        ];
    }
}

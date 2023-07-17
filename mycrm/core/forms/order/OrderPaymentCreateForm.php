<?php

namespace core\forms\order;

use core\helpers\company\PaymentHelper;
use core\models\Payment;
use yii\base\Model;

/**
 * @property $payment_id;
 * @property $amount;
 */
class OrderPaymentCreateForm extends Model
{
    private $insurance_company_id;

    public $payment_id;
    public $amount;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['payment_id', 'amount'], 'required'],
            [['payment_id', 'amount'], 'integer', 'min' => 0],
            [
                'payment_id',
                'validatePayment',
                'skipOnError' => true,
            ]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatePayment($attribute, $params)
    {
        $payment = Payment::findOne($this->{$attribute});

        if (!$payment) {
            $this->addError($attribute, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->getAttributeLabel($attribute)
            ]));
        } else {
            if ($payment->type == PaymentHelper::INSURANCE && !$this->insurance_company_id) {
                $this->addError($attribute, \Yii::t('app', "Insurance company has to be selected."));
            }
        }
    }

    /**
     * @param mixed $insurance_company_id
     */
    public function setInsuranceCompanyId($insurance_company_id)
    {
        $this->insurance_company_id = $insurance_company_id;
    }
}
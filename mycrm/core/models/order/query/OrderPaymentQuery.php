<?php

namespace core\models\order\query;

use core\helpers\company\PaymentHelper;
use yii\db\ActiveQuery;

class OrderPaymentQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function excludeInsurance()
    {
        if (!isset($this->joinWith['payment'])) {
            $this->joinWith('payment');
        }

        return $this->andWhere([
            'OR',
            ['!=', '{{%payments}}.type', PaymentHelper::INSURANCE],
            ['{{%payments}}.type' => null],
        ]);
    }

    /**
     * @return $this
     */
    public function excludeCashback()
    {
        if (!isset($this->joinWith['payment'])) {
            $this->joinWith('payment');
        }

        return $this->andWhere([
            'OR',
            ['!=', '{{%payments}}.type', PaymentHelper::CASHBACK],
            ['{{%payments}}.type' => null],
        ]);
    }

    /**
     * @return $this
     */
    public function onlyAccountable()
    {
        if (!isset($this->joinWith['payment'])) {
            $this->joinWith('payment');
        }

        return $this->andWhere([
            "OR",
            ['not in', '{{%payments}}.type', PaymentHelper::notAccountable()],
            ['{{%payments}}.type' => null]
        ]);
    }
}

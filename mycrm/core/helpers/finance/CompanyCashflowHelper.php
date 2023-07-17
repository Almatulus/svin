<?php

namespace core\helpers\finance;

use core\models\finance\CompanyCostItem;
use Yii;

class CompanyCashflowHelper
{
    /**
     * @param string $user_name
     * @param integer $remaining_debt
     *
     * @return string
     */
    public static function getDebtPaymentComment($user_name, $remaining_debt)
    {
        return Yii::t('app', 'Debt comment {user_name} {remaining_debt}',
            ['user_name' => $user_name, 'remaining_debt' => Yii::$app->formatter->asDecimal($remaining_debt)]);
    }
}

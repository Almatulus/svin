<?php

namespace core\helpers\customer;

use core\models\customer\CompanyCustomer;
use Yii;
use yii\helpers\ArrayHelper;

class CompanyCustomerHelper
{
    /**
     * @return array
     */
    public static function getRankLabels()
    {
        return [
            CompanyCustomer::RANK_NONE => Yii::t('app', 'Rank None'),
            CompanyCustomer::RANK_COPPER => Yii::t('app', 'Rank Copper'),
            CompanyCustomer::RANK_SILVER => Yii::t('app', 'Rank Silver'),
            CompanyCustomer::RANK_GOLD => Yii::t('app', 'Rank Gold'),
        ];
    }

    /**
     * @return array
     */
    public static function getSmsOptionLabels()
    {
        return [
            0 => 'Нет',
            1 => 'Да'
        ];
    }

    /**
     * @return array
     */
    public static function getRankIcon()
    {
        return [
            CompanyCustomer::RANK_GOLD => 'gold',
            CompanyCustomer::RANK_SILVER => 'silver',
            CompanyCustomer::RANK_COPPER => 'copper',
        ];
    }

    /**
     * @param $company_id
     * @return array
     */
    public static function getCities($company_id)
    {
        $customers = CompanyCustomer::find()->company($company_id)->andWhere(['<>', 'city', ''])->all();
        $cities = ArrayHelper::map($customers, 'city', 'city');

        return $cities;
    }
}
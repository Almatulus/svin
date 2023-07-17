<?php

namespace core\helpers\customer;

use core\models\customer\CustomerLoyalty;
use core\models\customer\loyalty\GrantCategoryProgram;
use core\models\customer\loyalty\GrantDiscountProgram;
use core\models\customer\loyalty\LoyaltyProgramInterface;
use core\models\customer\loyalty\RemoveCategoryProgram;
use core\models\customer\loyalty\RemoveDiscountProgram;
use Yii;

class CustomerLoyaltyHelper
{
    public static function getEventLabels()
    {
        return [
            CustomerLoyalty::EVENT_MONEY => Yii::t('app', 'Event Money'),
            CustomerLoyalty::EVENT_VISIT => Yii::t('app', 'Event Visit'),
            CustomerLoyalty::EVENT_DAY   => Yii::t('app', 'Event Day'),
        ];
    }

    public static function getEventLabel(int $event_id)
    {
        $labels = self::getEventLabels();

        return $labels[$event_id] ?? null;
    }

    public static function getModeLabels()
    {
        return [
            CustomerLoyalty::MODE_ADD_DISCOUNT    =>
                Yii::t('app', 'Add Discount'),
            CustomerLoyalty::MODE_REMOVE_DISCOUNT =>
                Yii::t('app', 'Remove Discount'),
            CustomerLoyalty::MODE_ADD_CATEGORY    =>
                Yii::t('app', 'Add Category'),
            CustomerLoyalty::MODE_REMOVE_CATEGORY =>
                Yii::t('app', 'Remove Category'),
        ];
    }

    public static function getModeLabel(int $model_id)
    {
        $labels = self::getModeLabels();

        return $labels[$model_id] ?? null;
    }

    /**
     * @param int $mode
     * @return LoyaltyProgramInterface
     */
    public static function getProgramInstance(int $mode): LoyaltyProgramInterface
    {
        switch ($mode) {
            case CustomerLoyalty::MODE_ADD_DISCOUNT:
                return new GrantDiscountProgram();
            case CustomerLoyalty::MODE_ADD_CATEGORY:
                return new GrantCategoryProgram();
            case CustomerLoyalty::MODE_REMOVE_DISCOUNT:
                return new RemoveDiscountProgram();
            case CustomerLoyalty::MODE_REMOVE_CATEGORY:
                return new RemoveCategoryProgram();
            default:
                throw new \InvalidArgumentException();
        }
    }
}

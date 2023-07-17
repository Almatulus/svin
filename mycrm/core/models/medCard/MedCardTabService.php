<?php

namespace core\models\medCard;

use core\models\user\User;
use core\models\division\DivisionService;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%med_card_tab_services}}".
 *
 * @property integer         $id
 * @property integer         $med_card_tab_id
 * @property integer         $division_service_id
 * @property integer         $quantity
 * @property integer         $discount
 * @property string          $price
 * @property integer         $created_user_id
 * @property string          $created_time
 * @property string          $deleted_time
 *
 * @property DivisionService $divisionService
 * @property MedCardTab      $medCardTab
 * @property User            $createdUser
 */
class MedCardTabService extends ActiveRecord
{
    /**
     * @param MedCardTab      $medCardTab
     * @param DivisionService $divisionService
     * @param User            $createdUser
     * @param integer         $quantity
     * @param integer         $discount
     * @param integer         $price
     *
     * @return MedCardTabService
     */
    public static function add(
        MedCardTab $medCardTab,
        DivisionService $divisionService,
        User $createdUser,
        $quantity,
        $discount,
        $price
    ) {
        $model = new MedCardTabService();
        $model->populateRelation('medCardTab', $medCardTab);
        $model->populateRelation('divisionService', $divisionService);
        $model->populateRelation('createdUser', $createdUser);
        $model->quantity = $quantity;
        $model->discount = $discount;
        $model->price    = $price;

        return $model;
    }

    /**
     * @param integer $quantity
     * @param integer $discount
     * @param integer $price
     */
    public function edit($quantity, $discount, $price)
    {
        $this->quantity = $quantity;
        $this->discount = $discount;
        $this->price    = $price;
    }

    /**
     * Set deleted
     */
    public function setDeleted()
    {
        $this->deleted_time = date('Y-m-d H:i:s');
    }

    /**
     * Revert deleted
     */
    public function revertDeleted()
    {
        $this->deleted_time = null;
    }

    /**
     * Returns total calculated price
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->price * (100 - $this->discount) / 100;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'                    => 'id',
            'med_card_tab_id'       => 'med_card_tab_id',
            'division_service_id'   => 'division_service_id',
            'division_service_name' => function (MedCardTabService $model) {
                return $model->divisionService->service_name;
            },
            'quantity'              => 'quantity',
            'discount'              => 'discount',
            'price'                 => function (MedCardTabService $model) {
                return number_format($model->price, 0, '', '');
            },
            'service_price'         => function () {
                return $this->divisionService->price;
            },
            'created_user_id'       => 'created_user_id',
            'created_user_name'     => function (MedCardTabService $model) {
                return $model->createdUser->getFullName();
            },
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_tab_services}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(),
            ['id' => 'division_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardTab()
    {
        return $this->hasOne(MedCardTab::className(),
            ['id' => 'med_card_tab_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var MedCardTab $medCardTab */
            if (isset($related['medCardTab'])
                && $medCardTab = $related['medCardTab']) {
                $medCardTab->save();
                $this->med_card_tab_id = $medCardTab->id;
            }

            /** @var DivisionService $divisionService */
            if (isset($related['divisionService'])
                && $divisionService = $related['divisionService']) {
                $divisionService->save();
                $this->division_service_id = $divisionService->id;
            }

            /** @var User $createdUser */
            if (isset($related['createdUser'])
                && $createdUser = $related['createdUser']) {
                $createdUser->save();
                $this->created_user_id = $createdUser->id;
            }

            return true;
        }

        return false;
    }
}

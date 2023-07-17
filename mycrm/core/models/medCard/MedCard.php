<?php

namespace core\models\medCard;

use core\models\order\Order;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%med_cards}}".
 *
 * @property integer      $id
 * @property integer      $order_id
 * @property integer      $number
 *
 * @property MedCardTab[] $tabs
 * @property Order        $order
 */
class MedCard extends ActiveRecord
{
    /**
     * @param int $order_id
     *
     * @return MedCard
     */
    public static function add(int $order_id)
    {
        $model           = new MedCard();
        $model->order_id = $order_id;

        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_cards}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'number'], 'integer'],
            [['order_id'], 'unique'],
            [
                ['order_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Order::className(),
                'targetAttribute' => ['order_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('app', 'ID'),
            'order_id'     => Yii::t('app', 'Order ID'),
            'number'       => Yii::t('app', 'Number'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id' => 'id',
            'order_id' => 'order_id',
            'number' => 'number',
            'tabs' => 'tabs',
            'services_total' => function (MedCard $model) {
                return array_reduce($model->tabs, function ($total, MedCardTab $model) {
                    return $total + $model->getServicesTotalPrice();
                }, 0);
            },
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTabs()
    {
        return $this->hasMany(MedCardTab::className(), ['med_card_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildTeeth()
    {
        return $this->hasMany(MedCardTooth::className(),
            ['med_card_tab_id' => 'id'])
            ->via('tabs')
            ->where(['type' => MedCardTooth::TYPE_CHILD])
            ->andWhere(['<>', 'diagnosis_id', null]);
    }
}

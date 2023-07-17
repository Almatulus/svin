<?php

namespace core\models\division;

use core\models\customer\Customer;
use core\models\division\query\DivisionReviewQuery;
use Yii;

/**
 * This is the model class for table "{{%division_reviews}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $division_id
 * @property string $created_time
 * @property integer $value
 * @property string $comment
 * @property integer $status
 *
 * @property Customer $customer
 * @property Division $division
 */
class DivisionReview extends \yii\db\ActiveRecord
{
    const STATUS_ENABLED = 0;
    const STATUS_DISABLED = 1;

    const REVIEW_LIMIT = 5;
    const REVIEW_AVERAGE = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_reviews}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'division_id', 'value'], 'required'],
            [['customer_id', 'division_id', 'status'], 'integer'],
            ['value', 'integer', 'min' => 0, 'max' => 5],
            [['comment'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer'),
            'division_id' => Yii::t('app', 'Division ID'),
            'created_time' => Yii::t('app', 'Created Time'),
            'value' => Yii::t('app', 'Value'),
            'comment' => Yii::t('app', 'Comments'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::updateDivisionRating($this->division);
    }

    /**
     * Update division rating
     * @param Division $division
     */
    public static function updateDivisionRating(Division $division)
    {
        $division->rating = self::getReviewValue($division);
        $division->save();
    }

    /**
     * Returns total value
     * @param Division $division
     * @return double
     */
    public static function getReviewValue(Division $division)
    {
        $reviewsCount = DivisionReview::find()->division($division->id)->count();
        $reviewsSum = DivisionReview::find()->division($division->id)->sum('value');

        if ($reviewsCount === 0)
        {
            $reviewsAverage = DivisionReview::REVIEW_AVERAGE;
        }
        else
        {
            $reviewsAverage = $reviewsSum / $reviewsCount;
        }

        $reviewValue = ($reviewsCount / ($reviewsCount + DivisionReview::REVIEW_LIMIT)) * $reviewsAverage +
            (DivisionReview::REVIEW_LIMIT / ($reviewsCount + DivisionReview::REVIEW_LIMIT)) * DivisionReview::REVIEW_AVERAGE;
        return doubleval($reviewValue);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new DivisionReviewQuery(get_called_class());
    }
}

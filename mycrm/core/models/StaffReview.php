<?php

namespace core\models;

use core\models\query\StaffReviewQuery;
use core\repositories\exceptions\AlreadyExistsException;
use Yii;
use core\models\customer\Customer;
use yii\helpers\Url;
use yii\web\Linkable;

/**
 * This is the model class for table "{{%reviews}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $staff_id
 * @property string $created_time
 * @property integer $value
 * @property string $comment
 * @property integer $status
 *
 * @property Customer $customer
 * @property Staff $staff
 */
class StaffReview extends \yii\db\ActiveRecord implements Linkable
{
    const STATUS_ENABLED = 0;
    const STATUS_DISABLED = 1;

    const REVIEW_LIMIT = 5;
    const REVIEW_AVERAGE = 3;

    /**
     * @param integer $customer_id
     * @param integer $staff_id
     * @param integer $value
     * @param string $comment
     * @return StaffReview
     */
    public static function add($customer_id, $staff_id, $value, $comment)
    {
        self::guardIsNotExist($customer_id, $staff_id);
        $review = new StaffReview();
        $review->customer_id = $customer_id;
        $review->staff_id = $staff_id;
        $review->value = $value;
        $review->comment = $comment;
        $review->status = StaffReview::STATUS_ENABLED;
        return $review;
    }

    /**
     * @param integer $value
     * @param string $comment
     * @return StaffReview
     */
    public function edit($value, $comment)
    {
        $this->guardIsNew();
        $this->value = $value;
        $this->comment = $comment;
        $this->status = StaffReview::STATUS_ENABLED;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'staff_id' => 'staff_id',
            'value' => 'value',
            'comment' => 'comment',
            'customer_id' => 'customer_id',
            'created_time' => 'created_time',
        ];
    }

    public function extraFields()
    {
        return [
            'customer' => 'customer',
            'staff' => 'staff',
        ];
    }

    public function getLinks()
    {
        return [
            'self' => Url::to(['/v2/staff/review/view', 'id' => $this->id], true),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_reviews}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer'),
            'staff_id' => Yii::t('app', 'Staff ID'),
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
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new StaffReviewQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @param integer $customer_id
     * @param integer $staff_id
     */
    private static function guardIsNotExist($customer_id, $staff_id)
    {
        if (StaffReview::find()->staff($staff_id)->customer($customer_id)->count() !== 0) {
            throw new AlreadyExistsException('Review exist');
        }
    }

    private function guardIsNew()
    {
        if ($this->isNewRecord) {
            throw new \DomainException('Interview is not new.');
        }
    }
}

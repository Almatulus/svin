<?php

namespace core\models\order;

use core\helpers\order\OrderConstants;
use core\helpers\OrderHistoryHelper;
use core\models\customer\Customer;
use core\models\user\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%order_history}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $created_time
 * @property integer $status
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $customer_comment
 * @property string $datetime
 * @property string $service_name
 * @property string $staff_name
 * @property string $staff_position
 * @property integer $type
 * @property integer $discount
 * @property integer $price
 * @property string $note
 * @property integer $action
 * @property integer $acting_user
 */
class OrderHistory extends \yii\db\ActiveRecord
{
    const ACTION_CREATE = 0;
    const ACTION_UPDATE = 1;
    const ACTION_CHECKOUT = 2;
    const ACTION_DISABLE = 3;
    const ACTION_RESET = 4;
    const ACTION_CANCEL = 5;
    const ACTION_WAIT = 6;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_history}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'created_time' => Yii::t('app', 'History Created Time'),
            'status' => Yii::t('app', 'Status'),
            'customer_name' => Yii::t('app', 'Customer Name'),
            'customer_phone' => Yii::t('app', 'Customer Phone'),
            'customer_comment' => Yii::t('app', 'Customer Comment'),
            'datetime' => Yii::t('app', 'Order model'),
            'service_name' => Yii::t('app', 'Service Name'),
            'staff_name' => Yii::t('app', 'Staff Name'),
            'staff_position' => Yii::t('app', 'Staff Position'),
            'type' => Yii::t('app', 'Type'),
            'discount' => Yii::t('app', 'Discount'),
            'price' => Yii::t('app', 'Price'),
            'note' => Yii::t('app', 'Note'),
            'action' => Yii::t('app', 'Action'),
            'acting_user' => Yii::t('app', 'Acting User'),
        ];
    }

    /**
     * @deprecated
     * @TODO Rewrite
     * Create history
     *
     * @param Order   $order
     * @param integer $action
     *
     * @return bool
     * @throws \Exception|\Throwable
     */
    public static function createHistory(Order $order, $action)
    {
        self::guardAction($action);

        /* @var Customer $customer */
        $customer = $order->companyCustomer->customer;

        $model = new OrderHistory();
        $model->order_id = $order->id;
        $model->created_time = (new \DateTime())->format("Y-m-d H:i:s");
        $model->status = $order->status;
        $model->customer_name = $customer->getFullName();
        $model->customer_phone = $customer->phone;
        $model->customer_comment = $order->companyCustomer->comments;
        $model->datetime = $order->datetime;
        $model->service_name = $order->servicesTitle;
        $model->staff_name = $order->staff->getFullName();
        $model->staff_position = implode(\core\models\company\CompanyPosition::STRING_DELIMITER,
            $order->staff->getCompanyPositions()->select('name')->column());
        $model->type = $order->type;
        $model->price = $order->price;
        $model->note = $order->note;
        $model->action = $action;
        $model->acting_user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity->username;
        return $model->insert(false);
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (empty($this->acting_user)) {
            return null;
        }

        return User::findOne(['username' => $this->acting_user]);
    }

    /**
     * Check if action if correct
     *
     * @param $action
     */
    private static function guardAction($action)
    {
        OrderHistoryHelper::getActionLabel($action);
    }

    public function fields()
    {
        return [
            'created_at' => 'created_time',
            'action'     => function () {
                return OrderHistoryHelper::getActionLabel($this->action);
            },
            'datetime',
            'status'     => function() {
                return OrderConstants::getStatuses()[$this->status];
            },
            'user'       => function () {
                $user = $this->getUser();
                return $user ? $user->getFullName()
                    : Yii::t('app', 'Undefined');
            },
            'staff_name',
            'status'
        ];
    }
}

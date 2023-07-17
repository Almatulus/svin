<?php

namespace core\models\order;

use common\components\HistoryBehavior;
use core\models\division\DivisionService;
use core\models\finance\CompanyCashflowService;
use core\models\finance\Payroll;
use Yii;

/**
 * This is the model class for table "{{%order_services}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $division_service_id
 * @property integer $discount
 * @property integer $duration
 * @property integer $price
 * @property integer $quantity
 * @property string $deleted_time
 *
 * @property DivisionService $divisionService
 * @property Order $order
 */
class OrderService extends \yii\db\ActiveRecord
{
    /**
     * @param Order           $order
     * @param DivisionService $divisionService
     * @param integer         $discount
     * @param integer         $duration
     * @param integer         $price
     * @param  integer        $quantity
     *
     * @return OrderService
     */
    public static function add(
        Order $order,
        DivisionService $divisionService,
        $discount,
        $duration,
        $price,
        $quantity
    ) {
        $model = new self();
        $model->populateRelation('order', $order);
        $model->populateRelation('divisionService', $divisionService);
        $model->discount = $discount;
        $model->duration = $duration;
        $model->price = $price;
        $model->quantity = $quantity;
        $model->deleted_time = null;
        return $model;
    }

    /**
     * @param integer $discount
     * @param integer $duration
     * @param integer $price
     */
    public function edit($discount, $duration, $price, $quantity)
    {
        $this->discount = $discount;
        $this->duration = $duration;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    /**
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function revertDeletion()
    {
        $this->deleted_time = null;
    }

    /**
     * Gets estimated price with discount
     * @return integer
     */
    public function getFinalPrice()
    {
        return $this->price * (100 - $this->discount) / 100;
    }

    /**
     * @return float|int
     */
    public function getDiscountPrice()
    {
        return $this->price * $this->discount / 100;
    }

    /**
     * @return string
     */
    public function getCashflowComment(): string
    {
        return Yii::t('app', 'Order {order_key}: {item_name}', [
            'order_key' => $this->order->number,
            'item_name' => $this->getName(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_services}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'order_id'            => Yii::t('app', 'Order ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'discount'            => Yii::t('app', 'Discount'),
            'quantity'            => Yii::t('app', 'Quantity'),
            'price'               => Yii::t('app', 'Price')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * Gets estimated price with discount
     * @return integer
     */
    public function getSalePrice()
    {
        return $this->price * (100 - $this->discount) / 100;
    }

    /**
     * @param OrderService[] $orderServices
     * @return array
     */
    public static function map($orderServices)
    {
        $data = [];
        foreach ($orderServices as $key => $orderService) {
            $data[$key] = [
                'id'                  => $orderService->divisionService->id,
                'name'                => $orderService->divisionService->fullName,
                'price'               => $orderService->price,
                'discount'            => $orderService->discount,
                'duration'            => $orderService->duration,
                'quantity'            => $orderService->quantity,
                'service_name'        => $orderService->divisionService->service_name,
                'service_price'       => $orderService->divisionService->price,
                'order_service_id'    => $orderService->id,
                'division_service_id' => $orderService->divisionService->id,
            ];
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $divisionService = $this->divisionService;

        return [
            'price',
            'discount',
            'duration',
            'quantity',
            'division_service_id',
            'order_service_id' => 'id',
            'id'               => 'division_service_id',
            'name'             => function () use ($divisionService) {
                return $divisionService->getFullName();
            },
            'service_name'     => function () use ($divisionService) {
                return $divisionService->service_name;
            },
            'service_price'    => function () use ($divisionService) {
                return $divisionService->price;
            },
        ];
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        $title = "";
        $categories = $this->getDivisionService()
            ->joinWith('categories', false)
            ->select('{{%service_categories}}.name')
            ->asArray()
            ->all();
        foreach ($categories as $key => $category) {
            $title .= $category['name'];
            if ($key != (sizeof($categories) - 1)) {
                $title .= ", ";
            }
        }
        return $title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->divisionService->service_name;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var Order $order */
            if (isset($related['order']) && $order = $related['order']) {
                $order->save();
                $this->order_id = $order->id;
            }
            /** @var DivisionService $divisionService */
            if (isset($related['divisionService']) && $divisionService = $related['divisionService']) {
                $divisionService->save();
                $this->division_service_id = $divisionService->id;
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
        ];
    }

    // ToDo Rewrite
    private $payroll;

    /**
     * @param Payroll $payroll
     */
    public function setPayroll(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * @return Payroll
     */
    public function getPayroll()
    {
        return $this->payroll;
    }

    /**
     * @return int
     */
    public function getPaymentAmount()
    {
        $payroll = $this->getPayroll();
        $payment_per_service = $this->getPayroll()
            ->calculateServicePayment(
                $this->division_service_id,
                $this->price,
                $this->discount
            );

        $scheme = $payroll->getPayrollScheme($this->division_service_id);

        return intval($payment_per_service * ($scheme->service_mode
            === Payroll::PAYROLL_MODE_PERCENTAGE ? 1 : $this->quantity));
    }

    /**
     * @return int
     */
    public function getPaymentPercent()
    {
        $payroll = $this->getPayroll();
        return $payroll->getServicePercent($this->division_service_id)
            * ($payroll->service_mode === Payroll::PAYROLL_MODE_PERCENTAGE ? 1
                : $this->quantity);
    }

    /**
     * @return int
     */
    public function getPaymentPrice()
    {
        return intval($this->getPayroll()->calculateFinalPrice($this->price, $this->discount));
    }
}



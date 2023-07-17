<?php

namespace core\models\order;

use common\components\events\PushNotificationEventHandler;
use common\components\HistoryBehavior;
use common\components\PushNotification;
use core\helpers\company\CashbackHelper;
use core\helpers\order\OrderConstants;
use core\helpers\order\OrderNotifier;
use core\models\company\Cashback;
use core\models\company\Referrer;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerRequestTemplate;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\File;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\InsuranceCompany;
use core\models\medCard\MedCard;
use core\models\order\query\OrderQuery;
use core\models\Staff;
use core\models\user\User;
use core\models\warehouse\Usage;
use DateTime;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * This is the model class for table "{{%orders}}".
 *
 * @property integer $id
 * @property integer $status
 * @property integer $company_customer_id
 * @property integer $type
 * @property string $created_time
 * @property integer $created_user_id
 * @property string $datetime
 * @property integer $price
 * @property string $note
 * @property integer $staff_id
 * @property integer $division_id
 * @property integer $notify_status
 * @property integer $hours_before
 * @property integer $duration
 * @property integer $company_cash_id
 * @property integer $insurance_company_id
 * @property integer $referrer_id
 * @property boolean $is_paid
 * @property boolean $services_disabled
 * @property string $color
 * @property string $google_event_id
 * @property integer $number
 * @property integer $payment_difference
 *
 * @property string $servicesTitle
 * @property boolean $ignore_stock
 * @property integer $productsPrice
 *
 * @property CompanyCustomer $companyCustomer
 * @property CompanyCustomer[] $contactCustomers
 * @property Referrer $referrer
 * @property CompanyCash $companyCash
 * @property MedCard $medCard
 * @property Staff $staff
 * @property Division $division
 * @property User $createdUser
 * @property InsuranceCompany $insuranceCompany
 * @property OrderHistory[] $orderHistory
 * @property OrderDocument[] $documents
 * @property File[] $files
 * @property OrderService[] $orderServices
 * @property OrderPayment[] $orderPayments
 * @property OrderProduct[] $orderProducts
 * @property DivisionService[] $divisionServices
 * @property Cashback[] $cashbacks
 * @property CompanyCashflow[] $cashflows
 * @property Usage $usage
 */
class Order extends \yii\db\ActiveRecord
{
    const EVENT_INSERT = 'modelInsert';
    const EVENT_UPDATE = 'modelUpdate';
    const EVENT_CHECKOUT = 'modelCheckout';
    const EVENT_DISABLE = 'modelDisable';
    const EVENT_RESET = 'modelReset';
    const EVENT_CANCEL = 'modelCancel';
    const EVENT_ENABLE = 'modelEnable';
    const EVENT_WAITING = 'modelWait';

    public $payments;
    public $products;
    public $services = [];

    protected $_orderNotifier = null;
    protected $_productsPrice = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (YII_ENV_PROD) {
            $this->on('model*', function ($event) {
                $this->sendWebNotification($event->name == self::EVENT_INSERT);
            });
        }

        // Insert
        if (YII_ENV_PROD) {
            $this->on(self::EVENT_INSERT, [$this, 'sendCreateNotifications']);
        }
        $this->on(self::EVENT_INSERT, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_CREATE);
        });

        // Update
        $this->on(self::EVENT_UPDATE, function ($event) {
            $changedAttributes = $this->getDirtyAttributes();
            if (!empty($changedAttributes)) {
                OrderHistory::createHistory($this, OrderHistory::ACTION_UPDATE);
            }
        });

        // Checkout
        if (YII_ENV_PROD) {
            $this->on(self::EVENT_CHECKOUT, [$this, 'sendCheckoutNotifications']);
        }
        $this->on(self::EVENT_CHECKOUT, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_CHECKOUT);
        });

        // Delete(Disable)
        if (YII_ENV_PROD) {
            $this->on(self::EVENT_DISABLE, [$this, 'sendCancelNotifications']);
        }
        $this->on(self::EVENT_DISABLE, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_DISABLE);
        });

        // cancel
        $this->on(self::EVENT_CANCEL, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_CANCEL);
        });

        // enable
        $this->on(self::EVENT_ENABLE, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_RESET);
        });

        // Reset
        $this->on(self::EVENT_RESET, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_RESET);
        });

        // In Waiting List
        $this->on(self::EVENT_WAITING, function ($event) {
            OrderHistory::createHistory($this, OrderHistory::ACTION_CREATE);
        });
    }

    /**
     * @param CompanyCustomer $companyCustomer
     * @param Division $division
     * @param Staff $staff
     * @param CompanyCash $companyCash
     * @param integer $type
     * @param integer $created_user_id
     * @param string $datetime
     * @param string $note
     * @param integer $hours_before
     * @param string $color
     * @param integer $insurance_company_id
     * @param integer $referrer_id
     *
     * @return Order
     */
    public static function add(
        CompanyCustomer $companyCustomer,
        Division $division,
        Staff $staff,
        CompanyCash $companyCash,
        $type,
        $created_user_id,
        $datetime,
        $note,
        $hours_before,
        $color,
        $insurance_company_id,
        $referrer_id
    ) {
        $model = new self();
        $model->populateRelation('companyCustomer', $companyCustomer);
        $model->populateRelation('staff', $staff);
        $model->populateRelation('division', $division);
        $model->populateRelation('companyCash', $companyCash);
        $model->type = $type;
        $model->created_user_id = $created_user_id;
        $model->datetime = $datetime;
        $model->note = $note;
        $model->hours_before = $hours_before;
        $model->color = $color;
        $model->insurance_company_id = $insurance_company_id;
        $model->referrer_id = $referrer_id;
        $model->notify_status = ($hours_before > 0) ? OrderConstants::NOTIFY_TRUE : OrderConstants::NOTIFY_FALSE;
        $model->status = OrderConstants::STATUS_ENABLED;
        $model->created_time = date('Y-m-d H:i:s');
        $model->price = 0;
        $model->duration = 0;
        $model->payment_difference = 0;
        $model->is_paid = false;
        $model->services_disabled = false;
        return $model;
    }

    /**
     * @param CompanyCustomer $companyCustomer
     * @param string $note
     * @param integer $hours_before
     * @param integer $company_cash_id
     * @param string $color
     * @param string $datetime
     * @param integer $insurance_company_id
     * @param integer $referrer_id
     */
    public function edit(
        CompanyCustomer $companyCustomer,
        $note,
        $hours_before,
        $company_cash_id,
        $color,
        $datetime,
        $insurance_company_id,
        $referrer_id
    ) {
        $this->populateRelation('companyCustomer', $companyCustomer);
        $this->note = $note;
        $this->company_cash_id = intval($company_cash_id);
        if( ! $this->isNotified() ){
            $this->notify_status = ($hours_before > 0) ? OrderConstants::NOTIFY_TRUE : OrderConstants::NOTIFY_FALSE;
        }
        $this->hours_before = intval($hours_before);
        $this->color = $color;
        $this->datetime = $datetime;
        $this->insurance_company_id = $insurance_company_id;
        $this->referrer_id = $referrer_id;
    }

    public function disableServices($value)
    {
        $this->services_disabled = $value;
    }

    /**
     * Set order contacts
     *
     * @param CompanyCustomer[] $contacts
     */
    public function setContacts($contacts)
    {
        $this->contactCustomers = $contacts;
    }

    /**
     * @param integer $duration
     */
    public function editDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @param integer $price
     */
    public function editPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @param Referrer $referrer
     */
    public function editReferrer(Referrer $referrer)
    {
        $this->populateRelation('referrer', $referrer);
    }

    /**
     * @param integer $payment_difference
     */
    public function editPaymentDifference($payment_difference)
    {
        $this->payment_difference = $payment_difference;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color ?: $this->staff->color;
    }

    /**
     * @param Staff $staff
     * @param DateTime $dateTime
     */
    public function move(Staff $staff, DateTime $dateTime)
    {
        $this->populateRelation('staff', $staff);
        $this->datetime = $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @throws \Exception
     */
    public function finish()
    {
        $this->guardFinish();
        $this->status = OrderConstants::STATUS_FINISHED;
    }

    public function enable()
    {
        $this->status = OrderConstants::STATUS_ENABLED;
    }

    public function disable()
    {
        $this->status = OrderConstants::STATUS_DISABLED;
    }

    public function cancel()
    {
        $this->status = OrderConstants::STATUS_CANCELED;
    }

    /**
     * @throws \Exception
     */
    public function reset()
    {
        $this->guardReset();
        $this->status = OrderConstants::STATUS_ENABLED;
    }

    /**
     * Returns whether debt
     *
     * @return boolean
     */
    public function debtExists()
    {
        return $this->payment_difference < 0;
    }

    /**
     * @return bool
     */
    public function depositExists()
    {
        return $this->payment_difference > 0;
    }

    public function needsFinanceRevert()
    {
        return $this->status === OrderConstants::STATUS_FINISHED;
    }

    public function needsNotification():bool
    {
        $current_time     = new DateTime();
        $order_time       = new DateTime($this->datetime);
        $seconds_difference = $order_time->getTimestamp() - $current_time->getTimestamp();
        $hours_difference = floor($seconds_difference / (60 * 60));

        return $hours_difference <= $this->hours_before;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%orders}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'status'              => Yii::t('app', 'Status'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'type'                => Yii::t('app', 'Order Type'),
            'created_time'        => Yii::t('app', 'Created Time'),
            'created_user_id'     => Yii::t('app', 'Created User'),
            'datetime'            => Yii::t('app', 'Datetime'),
            'price'               => Yii::t('app', 'Services cost currency'),
            'note'                => Yii::t('app', 'Note'),
            'staff_id'            => Yii::t('app', 'Staff ID'),
            'notify_status'       => Yii::t('app', 'Notify Status'),
            'hours_before'        => Yii::t('app', 'Hours Before'),
            'company_cash_id'     => Yii::t('app', 'Company Cash'),
            'number'              => Yii::t('app', 'Order Key'),
            'productsPrice'       => Yii::t('app', 'Products cost currency'),
            'services'            => Yii::t('app', 'Services'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'className' => function () {
                if ($this->isFinished()) {
                    return "past_event";
                } elseif ($this->isCanceled()) {
                    return "canceled_event";
                }

                return null;
            },
            'color'     => function () {
                return $this->isEditable() ? $this->getColor() : "";
            },
            'company_customer_id',
            'company_cash_id',
            'datetime',
            'division_id',
            'editable'       => function (Order $model) {
                return $model->isEditable();
            },
            'end'            => function (Order $model) {
                $finish_time = new DateTime($model->datetime);
                $finish_time->modify("+" . $model->duration . " minutes");
                return $finish_time->format("Y-m-d H:i:s");
            },
            'hours_before',
            'insurance_company_id',
            'note',
            'number',
            'referrer_id',
            'resourceId'     => 'staff_id',
            'payment_difference',
            'staff_id',
            'start'          => 'datetime',
            'status',
            'services_disabled',
            'status_name' => function () {
                return OrderConstants::getStatusLabel($this->status);
            },
            // To remove. Essential for pending orders in old design. Before removing should be handled
            'customer_name' => function () {
                return $this->companyCustomer->customer->name;
            },
            'customer_phone' => function (Order $model) {
                return $model->companyCustomer->customer->phone;
            },
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'cash'     => 'companyCash',
            'contactCustomers',
            'files',
            'documents',
            'customer' => 'companyCustomer',
            'history'  => 'orderHistory',
            'payments' => 'orderPayments',
            'products' => 'orderProducts',
            'services' => 'orderServices',
            'title'    => function (Order $model) {
                return $model->getTextInfo();
            },
            'source'   => function () {
                return $this->companyCustomer->source;
            },
            'staff',
            'medCard',
            'insuranceCompany',
            'referrer',

            // Refactor JS to remove below
            'staff_position' => function(){
                return implode(', ', ArrayHelper::getColumn($this->staff->companyPositions, 'name'));
            },
        ];
    }

    /**
     * Returns whether order is not completed
     *
     * @return bool
     */
    public function isEditable()
    {
        return !in_array($this->status, [
            OrderConstants::STATUS_FINISHED,
            OrderConstants::STATUS_CANCELED
        ]);
    }

    /**
     * Returns whether order is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status === OrderConstants::STATUS_ENABLED;
    }

    /**
     * Returns whether order is finished
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === OrderConstants::STATUS_FINISHED;
    }

    /**
     * Returns whether order is canceled
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->status === OrderConstants::STATUS_CANCELED;
    }

    /**
     * Returns whether order is in waiting list
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === OrderConstants::STATUS_WAITING;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'company_customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCard()
    {
        return $this->hasOne(MedCard::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::className(), ['id' => 'file_id'])
            ->viaTable('{{%order_files}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(OrderDocument::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insurance_company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderServices()
    {
        return $this->hasMany(OrderService::className(), ['order_id' => 'id'])
            ->andWhere(['{{%order_services}}.deleted_time' => null]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPayments()
    {
        return $this->hasMany(OrderPayment::className(), ['order_id' => 'id'])->orderBy(['payment_id' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['order_id' => 'id'])
            ->andWhere(['{{%order_service_products}}.deleted_time' => null]);
    }

    /**
     * @return \core\models\company\query\CashbackQuery|\yii\db\ActiveQuery
     */
    public function getCashbacks()
    {
        return $this->hasMany(Cashback::className(), ['id' => 'company_cashback_id'])
            ->viaTable('{{%order_cashbacks}}', ['order_id' => 'id'])
            ->onCondition(['{{%company_cashbacks}}.status' => CashbackHelper::STATUS_ENABLED])
            ->orderBy('id ASC');
    }

    /**
     * @return \core\models\finance\query\CashflowQuery
     */
    public function getCashflows()
    {
        return $this->hasMany(CompanyCashflow::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCash()
    {
        return $this->hasOne(CompanyCash::className(), ['id' => 'company_cash_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrer()
    {
        return $this->hasOne(Referrer::className(), ['id' => 'referrer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderHistory()
    {
        return $this->hasMany(OrderHistory::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactCustomers()
    {
        return $this->hasMany(CompanyCustomer::className(), ['id' => 'company_customer_id'])
            ->viaTable('{{%order_contacts_map}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsage()
    {
        return $this->hasOne(Usage::class, ['id' => 'usage_id'])
            ->viaTable('{{%order_usage}}', ['order_id' => 'id'])
            ->onCondition(['{{%warehouse_usage}}.status' => Usage::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $related = $this->getRelatedRecords();
            /** @var CompanyCustomer $companyCustomer */
            if (isset($related['companyCustomer']) && $companyCustomer = $related['companyCustomer']) {
                $companyCustomer->save();
                $this->company_customer_id = $companyCustomer->id;
            }
            /** @var Staff $staff */
            if (isset($related['staff']) && $staff = $related['staff']) {
                $staff->save();
                $this->staff_id = $staff->id;
            }
            /** @var Division $division */
            if (isset($related['division']) && $division = $related['division']) {
                $division->save();
                $this->division_id = $division->id;
            }
            /** @var CompanyCash $companyCash */
            if (isset($related['companyCash']) && $companyCash = $related['companyCash']) {
                $companyCash->save();
                $this->company_cash_id = $companyCash->id;
            }
            /** @var Referrer $referrer */
            if (isset($related['referrer']) && $referrer = $related['referrer']) {
                $referrer->save();
                $this->referrer_id = $referrer->id;
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getServicesTitle($separator = ', ')
    {
        return implode($separator, array_map(function (OrderService $orderService) {
            return $orderService->divisionService->service_name . " (" . $orderService->quantity . ")";
        }, $this->orderServices));
    }

    /**
     * Returns information about order
     * @return string
     */
    public function getTextInfo()
    {
        $customer = $this->companyCustomer->customer;
        $comment = empty($this->note) ? '' : "'{$this->note}'";
        $customerPhone = $this->staff->canSeeCustomerPhones() ? $customer->phone : Customer::maskPhone($customer->phone);

        return $customer->getFullName() . "\n"
            . $customerPhone . "\n"
            . $this->getServicesTitle(",\n") . "\n"
            . $comment . "\n";
    }

    /**
     * Set user notified
     */
    public function setNotified()
    {
        $this->notify_status = OrderConstants::NOTIFY_DONE;
    }

    /**
     * Returns whether customer is notified
     *
     * @return boolean
     */
    public function isNotified()
    {
        return $this->notify_status === OrderConstants::NOTIFY_DONE;
    }

    /**
     * Returns whether customer should be notified
     *
     * @return boolean
     */
    public function shouldNotify()
    {
        return $this->notify_status === OrderConstants::NOTIFY_TRUE;
    }

    /**
     * @return \DateTime
     */
    public function getNotifyTime()
    {
        if (!$this->shouldNotify()) {
            return null;
        }

        $orderTime = new DateTime($this->datetime);
        $orderTime->modify("-{$this->hours_before} hours");
        return $orderTime;
    }

    /**
     * @deprecated
     * Send notifications when order finished
     */
    public function sendCheckoutNotifications()
    {
        $orderNotifier = $this->getOrderNotifier();
        $message = 'Спасибо что пользуетесь нашими услугами. ' . 'Просим оценить услуги заведения!';
        $orderNotifier->sendMobilePush(
            PushNotificationEventHandler::EVENT_ORDER_STATUS_CONFIRMED,
            $this,
            $message,
            PushNotification::CALLBACK_DIVISION_REVIEW
        );

        if ($this->type == OrderConstants::TYPE_MANUAL) {
//            $orderNotifier->sendSMSNotification($this, $this->companyCustomer->customer->phone, CustomerRequestTemplate::TYPE_REQUEST_COMMENT_AFTER_VISIT_OFFLINE, true);
        } elseif ($this->type == OrderConstants::TYPE_APPLICATION) {
//            $orderNotifier->sendSMSNotification($this, $this->companyCustomer->customer->phone, CustomerRequestTemplate::TYPE_REQUEST_COMMENT_AFTER_VISIT_ONLINE, true);
        }
    }

    /**
     * @deprecated
     * Send notifications when order disabled
     */
    public function sendCancelNotifications()
    {
        $orderNotifier = $this->getOrderNotifier();
        $orderNotifier->sendSMSNotification($this, $this->companyCustomer->customer->phone,
            CustomerRequestTemplate::TYPE_NOTIFY_RECORD_REMOVAL);
//        $orderNotifier->sendSMSNotification($this, $this->staff->phone, CustomerRequestTemplate::TYPE_NOTIFY_STAFF_REMOVE_RECORD_INET);
    }

    /**
     * @deprecated
     * Send notifications when order created
     */
    public function sendCreateNotifications()
    {
        $orderNotifier = $this->getOrderNotifier();
        $orderNotifier->sendSMSNotification($this, $this->staff->phone,
            CustomerRequestTemplate::TYPE_NOTIFY_STAFF_NEW_RECORD_INET);
        if ($this->type == OrderConstants::TYPE_MANUAL) {
            $orderNotifier->sendSMSNotification($this, $this->companyCustomer->customer->phone,
                CustomerRequestTemplate::TYPE_NOTIFY_CLIENT_ABOUT_RECORD);
        } elseif ($this->type == OrderConstants::TYPE_APPLICATION) {
            $orderNotifier->sendSMSNotification($this, $this->companyCustomer->customer->phone,
                CustomerRequestTemplate::TYPE_REQUEST_WITH_RECORD_INFO);
//            $orderNotifier->sendSMSNotification($this, $this->division->phone, CustomerRequestTemplate::TYPE_NOTIFY_ADMIN_NEW_RECORD_INET);
        }
    }

    public function sendWebNotification(bool $insert)
    {
        if ($this->division->company->notify_about_order) {
            $this->getOrderNotifier()->sendWebNotification($this, $insert);
        }
    }

    /**
     * @return OrderNotifier
     */
    public function getOrderNotifier()
    {
        if ($this->_orderNotifier == null) {
            $this->_orderNotifier = new OrderNotifier();
        }
        return $this->_orderNotifier;
    }

    /**
     * Gets total price of products
     * @return double
     */
    public function getProductsPrice()
    {
        if ($this->_productsPrice == null) {
            $company = $this->companyCustomer->company;
            $this->_productsPrice = 0;
            foreach ($this->orderProducts as $product) {
                $this->_productsPrice += $product->getTotalSellingPrice();
            }
        }
        return $this->_productsPrice;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }

    /**
     * Check if order status is ok to finish
     */
    private function guardFinish()
    {
        if ($this->status !== OrderConstants::STATUS_ENABLED) {
            throw new \Exception(Yii::t('app', 'Enable to checkout'));
        }
    }

    /**
     * Check if order status is not enabled
     */
    private function guardReset()
    {
        if ($this->status === OrderConstants::STATUS_ENABLED) {
            throw new \Exception(Yii::t('app', 'Cannot reset'));
        }
    }

    /**
     * @return integer
     */
    public function getPaidTotal()
    {
        return $this->isFinished() ? ($this->price + $this->payment_difference) : 0;
    }

    /**
     * @return integer
     */
    public function getIncome()
    {
        return $this->price - $this->getOrderProducts()->sum('purchase_price * quantity');
    }

    /**
     * Returns cash payment
     *
     * @return integer
     */
    public function getIncomeCash()
    {
        return intval($this->getOrderPayments()->sum('amount'));
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::className(),
                'relations' => [
                    'contactCustomers',
                    'orderPayments',
                    'orderProducts',
                    'orderServices',
                ],
            ],
        ];
    }

    /**
     * @return OrderPayment|mixed
     */
    public function getPaid()
    {
        return array_reduce($this->orderPayments, function (int $sum, OrderPayment $payment) {
            return $sum + (!$payment->payment->isAccountable() ? 0 : $payment->amount);
        }, 0);
    }

    /**
     * @return OrderPayment|mixed
     */
    public function getDepositPayment()
    {
        return array_reduce($this->orderPayments, function (int $sum, OrderPayment $payment) {
            return $sum + ($payment->payment->isDeposit() ? $payment->amount : 0);
        }, 0);
    }

    /**
     * @return OrderPayment|mixed
     */
    public function getCashbackPayment()
    {
        return array_reduce($this->orderPayments, function (int $sum, OrderPayment $payment) {
            return $sum + ($payment->payment->isCashBack() ? $payment->amount : 0);
        }, 0);
    }

    /**
     * @return bool
     */
    public function getPaymentExcess()
    {
        return array_reduce($this->orderPayments, function (int $sum, OrderPayment $payment) {
                if ($payment->payment->isDeposit()) {
                    return $sum;
                }
                return $sum + $payment->amount;
            }, 0) - $this->price;
    }

    /**
     * @param OrderService[] $orderServices
     */
    public function setServices($orderServices)
    {
        $this->orderServices = $orderServices;
    }

    /**
     * @param OrderProduct[] $orderProducts
     */
    public function setProducts($orderProducts)
    {
        $this->orderProducts = $orderProducts;
    }

    /**
     * @param OrderPayment[] $orderPayments
     */
    public function setPayments($orderPayments)
    {
        $this->orderPayments = $orderPayments;
    }

    /**
     * @param $amount
     * @return array
     */
    public function getExcessivePayments($amount)
    {
        $payments = [];
        $sum = 0;
        $price = $this->price;
        foreach ($this->orderPayments as $orderPayment) {
            if ($amount <= 0) {
                break;
            }

            if (!$orderPayment->payment->isAccountable()) {
                continue;
            }

            $sum += $orderPayment->amount;
            if ($sum > $price) {
                $excess = min($sum - $price, $amount);
                $amount -= $excess;
                $sum -= $orderPayment->amount;
                $price = 0;

                $payments[$orderPayment->payment_id] = $excess;
            }
        }

        return $payments;
    }


    /**
     * @param array $payments
     */
    public function subtractDebtPayment(array $payments)
    {
        foreach ($this->orderPayments as $orderPayment) {
            if (isset($payments[$orderPayment->payment_id])) {
                $orderPayment->amount -= $payments[$orderPayment->payment_id];
            }
        }
    }

    /**
     * @throws ForbiddenHttpException
     * @throws \Exception
     */
    public function guardPassedDate()
    {
        $date = new DateTime($this->datetime);
        $today = new DateTime(date('Y-m-d H:i:s', strtotime('today midnight')));

        if ($date < $today && !$this->canAdminOrder()) {
            throw new ForbiddenHttpException('Not Allowed to update. The date is passed');
        }
    }

    /**
     * @return boolean
     */
    public function canAdminOrder()
    {
        $staff = Yii::$app->user->identity->staff;
        return Yii::$app->user->can("administrator") || ($staff && $staff->can_update_order);
    }
}

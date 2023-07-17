<?php

namespace core\models\company;

use common\components\HistoryBehavior;
use core\helpers\CompanyHelper;
use core\models\company\query\CompanyQuery;
use core\models\CompanyNoticesInfo;
use core\models\CompanyPaymentLog;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerLoyalty;
use core\models\customer\CustomerRequestTemplate;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCostItem;
use core\models\Image;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\order\Order;
use core\models\ServiceCategory;
use core\models\user\User;
use core\models\webcall\WebCall;
use DateTime;
use Yii;

/**
 * This is the model class for table "{{%companies}}".
 *
 * @property integer                 $id
 * @property string                  $name
 * @property integer                 $logo_id
 * @property integer                 $status
 * @property string                  $head_name
 * @property string                  $head_surname
 * @property string                  $head_patronymic
 * @property integer                 $category_id
 * @property integer                 $tariff_id
 * @property double                  $balance
 * @property boolean                 $publish
 * @property string                  $address
 * @property string                  $phone
 * @property string                  $iik
 * @property string                  $bik
 * @property string                  $bin
 * @property string                  $bank
 * @property string                  $license_issued
 * @property string                  $license_number
 * @property string                  $widget_prefix
 * @property integer                 $file_manager_enabled
 * @property integer                 $show_referrer
 * @property boolean                 $show_new_interface
 * @property integer                 $interval
 * @property string                  $online_start
 * @property string                  $online_finish
 * @property boolean                 $unlimited_sms
 * @property integer $cashback_percent
 * @property boolean $notify_about_order
 * @property boolean $limit_auth_time_by_schedule
 * @property boolean $enable_integration
 *
 * @property Image                   $logo
 * @property ServiceCategory         $category
 * @property CompanyCostItem[]       $costItems
 * @property CompanyCash[]           $companyCashes
 * @property CompanyPosition[]       $companyPositions
 * @property CompanyCustomer[]       $companyCustomers
 * @property CustomerLoyalty[] $loyaltyPrograms
 * @property Division[]              $divisions
 * @property MedCardToothDiagnosis[] $teethDiagnoses
 * @property CompanyNoticesInfo      $noticesInfo
 * @property Tariff                  $tariff
 * @property TariffPayment           $lastTariffPayment
 * @property TariffPayment[]         $tariffPayments
 * @property WebCall                 $webcall
 * @property User[]                  $users
 * @property CompanyPaymentLog[]     $confirmedPaymentLogs
 */
class Company extends \yii\db\ActiveRecord
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const PUBLISH_FALSE = 0;
    const PUBLISH_TRUE = 1;

    const CALCULATE_STRAIGHT = 0;
    const CALCULATE_REVERSE = 1;

    const MESSAGING_SMS = 1; // SMS
    const MESSAGING_WA = 2; // WhatsApp

    /**
     * @param string $company_name
     * @param integer $status
     * @param boolean $publish
     * @param integer $category_id
     * @param string $head_name
     * @param string $head_surname
     * @param string $head_patronymic
     * @param integer $tariff_id
     * @param string $address
     * @param string $bank
     * @param string $bik
     * @param string $bin
     * @param string $iik
     * @param string $license_issued
     * @param string $license_number
     * @param string $phone
     * @param integer $file_manager_enabled
     * @param boolean $show_referrer
     * @param integer $interval
     * @param boolean $unlimited_sms
     * @param boolean $notify_about_order
     *
     * @param $limit_auth_time_by_schedule
     * @param $enable_integration
     * @return Company
     */
    public static function add(
        $company_name,
        $status,
        $publish,
        $category_id,
        $head_name,
        $head_surname,
        $head_patronymic,
        $tariff_id,
        $address,
        $bank,
        $bik,
        $bin,
        $iik,
        $license_issued,
        $license_number,
        $phone,
        $file_manager_enabled,
        $show_referrer,
        $interval,
        $unlimited_sms,
        $notify_about_order,
        $limit_auth_time_by_schedule,
        $enable_integration = false
    ) {
        $model                       = new Company();
        $model->name                 = $company_name;
        $model->logo_id              = 1;
        $model->status               = $status;
        $model->publish              = $publish;
        $model->head_name            = $head_name;
        $model->head_surname         = $head_surname;
        $model->head_patronymic      = $head_patronymic;
        $model->category_id          = $category_id;
        $model->tariff_id            = $tariff_id;
        $model->address              = $address;
        $model->bank                 = $bank;
        $model->bik                  = $bik;
        $model->bin                  = $bin;
        $model->iik                  = $iik;
        $model->license_issued       = $license_issued;
        $model->license_number       = $license_number;
        $model->phone                = $phone;
        $model->file_manager_enabled = $file_manager_enabled;
        $model->show_referrer        = $show_referrer;
        $model->interval             = $interval;
        $model->unlimited_sms        = $unlimited_sms;
        $model->notify_about_order = $notify_about_order;
        $model->limit_auth_time_by_schedule = $limit_auth_time_by_schedule;
        return $model;
    }

    /**
     * @param string $company_name
     * @param integer $status
     * @param boolean $publish
     * @param integer $category_id
     * @param string $head_name
     * @param string $head_surname
     * @param string $head_patronymic
     * @param integer $tariff_id
     * @param string $address
     * @param string $bank
     * @param string $bik
     * @param string $bin
     * @param string $iik
     * @param string $license_issued
     * @param string $license_number
     * @param string $phone
     * @param integer $file_manager_enabled
     * @param boolean $show_referrer
     * @param integer $interval
     * @param boolean $unlimited_sms
     * @param boolean $notify_about_order
     * @param $limit_auth_time_by_schedule
     * @param $enable_integration
     */
    public function edit(
        $company_name,
        $status,
        $publish,
        $category_id,
        $head_name,
        $head_surname,
        $head_patronymic,
        $tariff_id,
        $address,
        $bank,
        $bik,
        $bin,
        $iik,
        $license_issued,
        $license_number,
        $phone,
        $file_manager_enabled,
        $show_referrer,
        $interval,
        $unlimited_sms,
        $notify_about_order,
        $limit_auth_time_by_schedule,
        $enable_integration
    ) {
        $this->rename(
            $company_name,
            $head_name,
            $head_surname,
            $head_patronymic
        );
        $this->status               = $status;
        $this->publish              = $publish;
        $this->category_id          = $category_id;
        $this->tariff_id            = $tariff_id;
        $this->file_manager_enabled = $file_manager_enabled;
        $this->show_referrer        = $show_referrer;
        $this->interval             = $interval;
        $this->editDetails(
            $address,
            $bank,
            $bik,
            $bin,
            $iik,
            $license_issued,
            $license_number,
            $phone,
            $this->logo_id
        );
        $this->unlimited_sms = $unlimited_sms;
        $this->notify_about_order = $notify_about_order;
        $this->limit_auth_time_by_schedule = $limit_auth_time_by_schedule;
        $this->enable_integration = $enable_integration;
    }

    /**
     * @param string  $address
     * @param string  $bank
     * @param string  $bik
     * @param string  $bin
     * @param string  $iik
     * @param string  $license_issued
     * @param string  $license_number
     * @param string  $phone
     * @param integer $logo_id
     * @param string  $widget_prefix
     * @param string  $online_start
     * @param string  $online_finish
     */
    public function editDetails(
        $address,
        $bank,
        $bik,
        $bin,
        $iik,
        $license_issued,
        $license_number,
        $phone,
        $logo_id,
        $widget_prefix = null,
        $online_start = null,
        $online_finish = null
    ) {
        $this->address        = $address;
        $this->bank           = $bank;
        $this->bik            = $bik;
        $this->bin            = $bin;
        $this->iik            = $iik;
        $this->license_issued = $license_issued;
        $this->license_number = $license_number;
        $this->phone          = $phone;
        $this->logo_id        = $logo_id;
        $this->widget_prefix  = $widget_prefix;
        $this->online_start   = $online_start;
        $this->online_finish  = $online_finish;
    }

    /**
     * @param $company_name
     * @param $head_name
     * @param $head_surname
     * @param $head_patronymic
     */
    public function rename(
        $company_name,
        $head_name,
        $head_surname,
        $head_patronymic
    ) {
        $this->name            = $company_name;
        $this->head_name       = $head_name;
        $this->head_surname    = $head_surname;
        $this->head_patronymic = $head_patronymic;
    }

    /**
     * @param bool $have_new_interface
     */
    public function setNewInterface(bool $have_new_interface)
    {
        $this->show_new_interface = $have_new_interface;
    }

    /**
     * @return string
     */
    public function getOnlineWidgetLink()
    {
        if ( ! $this->publish) {
            return null;
        }

        return "http://".($this->widget_prefix ? $this->widget_prefix
                : $this->id).".".Yii::$app->params['online_widget_host'];
    }

    /**
     * @return string
     */
    public function getCeoName()
    {
        return trim($this->head_name) . " "
            . trim($this->head_surname) . " "
            . trim($this->head_patronymic);
    }

    public function isActive()
    {
        return intval($this->status) === self::STATUS_ENABLED;
    }

    public function setupWebCall(bool $enable_webcall = true): WebCall
    {
        return $this->webcall ?? WebCall::add($this, $enable_webcall);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%companies}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'balance'            => Yii::t('app', 'Balance'),
            'name'               => Yii::t('app', 'Name'),
            'logo_id'            => Yii::t('app', 'Logo ID'),
            'status'             => Yii::t('app', 'Status'),
            'head_name'          => Yii::t('app', 'Head Name'),
            'head_surname'       => Yii::t('app', 'Head Surname'),
            'head_patronymic'    => Yii::t('app', 'Head Patronymic'),
            'category_id'        => Yii::t('app', 'Category ID'),
            'publish'            => Yii::t('app', 'Publish'),
            'tariff_id'          => Yii::t('app', 'Tariff'),
            'unlimited_sms'      => Yii::t('app', 'Unlimited SMS'),
            'notify_about_order' => Yii::t('app', 'Receive web notifications about order creation.'),
            'cashback_percent'   => Yii::t('app', 'Cashback Percent')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogo()
    {
        return $this->hasOne(Image::class, ['id' => 'logo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ServiceCategory::class,
            ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebcall()
    {
        return $this->hasOne(WebCall::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfirmedPaymentLogs()
    {
        return $this->hasMany(CompanyPaymentLog::class, ['company_id' => 'id'])
            ->andWhere('{{%company_payment_log}}.confirmed_time IS NOT NULL');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|\core\models\company\query\TariffPaymentQuery
     */
    public function getTariffPayments()
    {
        return $this->hasMany(TariffPayment::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|\core\models\company\query\TariffPaymentQuery
     */
    public function getLastTariffPayment()
    {
        return $this->hasOne(TariffPayment::class, ['company_id' => 'id'])
            ->orderBy(['{{%company_tariff_payments}}.start_date' => SORT_DESC, 'period' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeethDiagnoses()
    {
        return $this->hasMany(MedCardToothDiagnosis::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisions()
    {
        return $this->hasMany(Division::class, ['company_id' => 'id'])
                    ->andWhere(['{{%divisions}}.status' => Division::STATUS_ENABLED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCashes()
    {
        return $this->hasMany(CompanyCash::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCostItems()
    {
        return $this->hasMany(CompanyCostItem::class, ['company_id' => 'id']);
    }

    /**
     * @return \core\models\company\query\CompanyPositionQuery
     */
    public function getCompanyPositions()
    {
        /** @var \core\models\company\query\CompanyPositionQuery $query */
        $query = $this->hasMany(CompanyPosition::class,
            ['company_id' => 'id']);
        return $query->notDeleted();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomers()
    {
        return $this->hasMany(CompanyCustomer::class,
            ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoyaltyPrograms()
    {
        return $this->hasMany(CustomerLoyalty::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequestTemplates()
    {
        return $this->hasMany(CustomerRequestTemplate::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastOrder()
    {
        return $this->hasOne(Order::class, ['division_id' => 'id'])->via('divisions')
            ->orderBy('created_time DESC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastTask()
    {
        return $this->hasOne(Task::class, ['company_id' => 'id'])
            ->andWhere(['{{%company_tasks}}.end_date' => null])
            ->orderBy('{{%company_tasks}}.due_date DESC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }

    /**
     * @return CompanyNoticesInfo|array|null|\yii\db\ActiveRecord
     */
    public function getNoticesInfo()
    {
        $noticesInfo = CompanyNoticesInfo::find()
                                         ->where(['company_id' => $this->id])
                                         ->one();
        if ( ! $noticesInfo) {
            $noticesInfo             = new CompanyNoticesInfo();
            $noticesInfo->company_id = $this->id;
            $noticesInfo->save();
        }

        return $noticesInfo;
    }

    /**
     * Gets limit of sms
     *
     * @return integer
     */
    public function getSmsLimit()
    {
        return intval($this->getBalance() / Yii::$app->params['sms_cost']);
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return intval($this->getConfirmedPaymentLogs()->sum('value'));
    }

    /**
     * Create payment
     *
     * @param integer $payment_amount
     * @param string $description
     *
     * @param null $customer_request_id
     * @return bool
     */
    public function createPayment($payment_amount, $description, $customer_request_id = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $log = CompanyPaymentLog::add(
                $this->id,
                CompanyPaymentLog::CURRENCY_KZT,
                $description,
                null,
                min($payment_amount, 0 - $payment_amount),
                true,
                $customer_request_id
            );

            if ( ! $log->save()) {
                throw new \Exception(json_encode($log->getErrors()));
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Set company disabled
     *
     * @return boolean
     */
    public function disable()
    {
        $this->status = self::STATUS_DISABLED;

        return $this->save();
    }

    /**
     * Set company enabled
     *
     * @return boolean
     */
    public function enable()
    {
        $this->status = self::STATUS_ENABLED;

        return $this->save();
    }

    /**
     * @return string
     */
    public function getMaxWorkingTime()
    {
        return $this->getDivisions()->permitted()->enabled()
                    ->max('working_finish');
    }

    /**
     * @return null|string
     */
    public function getCategoryLabel()
    {
        $category = $this->category;
        return $category ? $category->name : null;
    }

    /**
     * @return string
     */
    public function getMinWorkingTime()
    {
        return $this->getDivisions()->permitted()->enabled()
                    ->min('working_start');
    }

    /**
     * @return array
     */
    public function getDivisionsDefaultNotificationTimeList()
    {
        $result = [];
        foreach ($this->divisions as $division) {
            $result[$division->id] = $division->default_notification_time;
        }
        return $result;
    }

    /**
     * @deprecated
     *
     * @param string|null $start
     * @param string|null $end
     * @param string|null $staffs
     *
     * @return array
     * [
     *      'min' => company working start time (H:i)
     *      'max' => company working start time (H:i)
     * ]
     */
    public function getWorkingPeriod($start = null, $end = null, $staffs = null)
    {
        $companyStartWorking  = new DateTime($this->getMinWorkingTime());
        $companyFinishWorking = new DateTime($this->getMaxWorkingTime());

        $startDate = new \DateTime($start);
        $endDate   = new \DateTime($end);

        $orderQuery = Order::find()
            ->enabled()
            ->company()
            ->startFrom($startDate)
            ->to($endDate)
            ->andFilterWhere(['{{%orders}}.staff_id' => $staffs]);

        $min_order_time = $orderQuery->min('datetime');
        $max_order_time = $orderQuery->max('datetime');

        if ( ! empty($min_order_time)) {
            $minTime = new DateTime($min_order_time);
            $minTime->setDate(date('Y'), date('m'), date('d'));
            $companyStartWorking = min($companyStartWorking, $minTime);
        }

        if ( ! empty($max_order_time)) {
            $maxTime = new DateTime($max_order_time);
            $maxTime->setDate(date('Y'), date('m'), date('d'));
            $companyFinishWorking = max($companyFinishWorking, $maxTime);
        }

        return [
            'min' => $companyStartWorking->format('H:i'),
            'max' => str_replace('00:00', '24:00', $companyFinishWorking->format('H:i'))
        ];
    }

    /**
     * @param DateTime $date_time
     *
     * @return bool
     */
    public function isOnlineAvailable(Datetime $date_time)
    {
        $check_time = DateTime::createFromFormat(
            'H:i',
            $date_time->format('H:i')
        );

        $valid = true;
        if ( ! empty($this->online_start)) {
            $online_start_time = DateTime::createFromFormat(
                'H:i:s',
                $this->online_start
            );

            $valid = $valid && $online_start_time <= $check_time;
        }

        if ( ! empty($this->online_finish)) {
            $online_finish_time = DateTime::createFromFormat(
                'H:i:s',
                $this->online_finish
            );

            $valid = $valid && $online_finish_time >= $check_time;
        }

        return $valid;
    }

    /**
     * Generates time range with interval between two dates
     *
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return array
     */
    public function getTimeRangeHours(\DateTime $start = null, \DateTime $end = null)
    {
        if ($start == null) {
            $start = new \DateTime($this->getMinWorkingTime());
        }
        if ($end == null) {
            $end = new \DateTime($this->getMaxWorkingTime());
        }

        $result = [$start->format('H') => $start->format('H')];
        while ($start < $end) {
            $start->modify("+1 hour");
            if ($start->format('H') == '00') {
                $result["24"] = "24";
            } else {
                $result[$start->format('H')] = $start->format('H');
            }
        }

        return $result;
    }

    /**
     * Check if web call setup finished
     *
     * @return boolean
     */
    public function webCallSetupFinished()
    {
        return WebCall::find()->where(['company_id' => $this->id])
                      ->andWhere('api_key IS NOT NULL AND username IS NOT NULL AND domain IS NOT NULL')
                      ->exists();
    }

    /**
     * Check whether company has access to web call module
     *
     * @return boolean
     */
    public function hasWebCallAccess()
    {
        return WebCall::find()->where([
            'company_id' => $this->id,
            'enabled'    => true
        ])->exists();
    }

    /**
     * Check if company has enough balance to send sms and create payment
     *
     * @param string $message
     * @return bool
     */
    public function canSendSms($message)
    {
        $paymentAmount = CompanyHelper::estimateSmsPrice(strval($message));
        return $this->isActive() && ($this->unlimited_sms || $this->hasEnoughBalance($paymentAmount));
    }

    /**
     * Returns whether company has enough balance pay
     *
     * @param integer $payment_value
     *
     * @return bool
     */
    public function hasEnoughBalance($payment_value)
    {
        return $this->getBalance() >= $payment_value;
    }

    /**
     * Check if company can upload files
     *
     * @return bool
     */
    public function canUploadFiles()
    {
        return $this->file_manager_enabled;
    }

    /**
     * Check if company can view files
     *
     * @return bool
     */
    public function canViewFiles()
    {
        return $this->file_manager_enabled
               || Order::find()
                       ->company()
                       ->joinWith('files', false, 'INNER JOIN')
                       ->exists();
    }

    /**
     * Check whether company can manage teeth care
     *
     * @return bool
     */
    public function canManageTeethCare()
    {
        $accessible_categories = [
            ServiceCategory::ROOT_STOMATOLOGY,
            ServiceCategory::ROOT_CLINIC,
        ];

        return Division::find()
                       ->company()
                       ->enabled()
                       ->category($accessible_categories)
                       ->permitted()
                       ->exists();
    }

    /**
     * Check whether company can manage medical care
     *
     * @return bool
     */
    public function canManageMedicalCard()
    {
        $accessible_categories = [
            ServiceCategory::ROOT_STOMATOLOGY,
            ServiceCategory::ROOT_CLINIC,
        ];

        return Division::find()
                       ->company()
                       ->enabled()
                       ->category($accessible_categories)
                       ->permitted()
                       ->exists();
    }

    /**
     * Check whether company is in medical category
     *
     * @return bool
     */
    public function isMedCategory(): bool
    {
        return in_array($this->category_id, [
            ServiceCategory::ROOT_STOMATOLOGY,
            ServiceCategory::ROOT_CLINIC
        ]);
    }

    /**
     * Check whether company is in stomatology category
     *
     * @return bool
     */
    public function isStomCategory(): bool
    {
        return $this->category_id === ServiceCategory::ROOT_STOMATOLOGY;
    }

    /**
     * @return bool
     */
    public function hasFreeStaffSlots()
    {
        if (!$this->tariff) {
            return false;
        }

        $userCount = User::find()->distinct()
            ->company($this->id)
            ->excludeRole('administrator')
            ->enabled()
            ->count();

        if ($this->tariff->staff_qty <= $userCount) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id'                   => 'id',
            'name'                 => 'name',
            'head_name'            => 'head_name',
            'head_surname'         => 'head_surname',
            'head_patronymic'      => 'head_patronymic',
            'status'               => 'status',
            'status_label'       => function (Company $model) {
                return CompanyHelper::getStatusLabel($model->status);
            },
            'logo_id'            => 'logo_id',
            'logo_path'          => function (Company $model) {
                return $model->getLogoPath();
            },
            'category_id'        => 'category_id',
            'category_label'     => function (Company $model) {
                return $model->getCategoryLabel();
            },
            'publish'            => 'publish',
            'balance'            => 'balance',
            'last_payment'       => function (Company $model) {
                return $model->lastTariffPayment->start_date ?? null;
            },
            'tariff'             => 'tariff',
            'address'            => 'address',
            'iik'                => 'iik',
            'bank'               => 'bank',
            'bin'                => 'bin',
            'bik'                => 'bik',
            'phone'              => 'phone',
            'show_new_interface',
            'license_issued'       => 'license_issued',
            'license_number'       => 'license_number',
            'widget_prefix'        => 'widget_prefix',
            'widget_url'           => function (Company $model) {
                return $model->getOnlineWidgetLink();
            },
            'file_manager_enabled' => 'file_manager_enabled',
            'show_referrer'        => 'show_referrer',
            'interval'             => 'interval',
            'online_start'         => 'online_start',
            'online_finish'        => 'online_finish',
            'cashback_percent'
        ];
    }

    public function extraFields()
    {
        return [
            'category'  => 'category',
            'cashes'    => 'companyCashes',
            'positions' => 'companyPositions',
            'divisions' => 'divisions',
            'users'     => 'users',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CompanyQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::class,
        ];
    }

    /**
     * @return array
     */
    public static function map()
    {
        return \yii\helpers\ArrayHelper::map(Company::find()->orderBy('name ASC')->asArray()->all(), 'id', 'name');
    }

    /**
     * @return null|string
     */
    public function getLogoPath()
    {
        if (empty($this->logo_id)) {
            return null;
        }

        return \Yii::$app->params['api_host'] . $this->logo->getPath();
    }
}

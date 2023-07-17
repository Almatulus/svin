<?php

namespace core\models;

use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\helpers\StaffHelper;
use core\models\company\CompanyPosition;
use core\models\company\query\CompanyPositionQuery;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCashflow;
use core\models\finance\PayrollStaff;
use core\models\order\Order;
use core\models\order\query\OrderQuery;
use core\models\query\StaffQuery;
use core\models\user\User;
use core\models\user\UserDivision;
use core\models\warehouse\Sale;
use core\models\warehouse\Usage;
use core\repositories\exceptions\NotFoundException;
use Datetime;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Linkable;

/**
 * This is the model class for table "{{%staffs}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property string $description
 * @property string $birth_date
 * @property integer $image_id
 * @property integer $document_scan_id
 * @property string $surname
 * @property integer $gender
 * @property string $iin
 * @property string $description_private
 * @property string $phone
 * @property integer $has_calendar
 * @property string $color
 * @property integer $user_id
 * @property array $user_permissions
 * @property string $payment_date
 * @property boolean $see_own_orders
 * @property boolean $can_update_order
 * @property boolean $create_order
 * @property boolean $see_customer_phones
 * @property string $code_1c
 *
 * @property CompanyCashflow[] $companyCashflows
 * @property Order[] $forthcomingOrders
 * @property Order[] $orders
 * @property DivisionService[] $divisionServices
 * @property StaffReview[] $staffReviews
 * @property StaffSchedule[] $staffSchedules
 * @property CompanyPosition $companyPosition
 * @property CompanyPosition[] $companyPositions
 * @property Division[] $divisions
 * @property User $user
 * @property UserDivision[] $userDivisions
 * @property Image $image
 * @property Image $documentScan
 * @property PayrollStaff[] $staffPayrolls
 * @property StaffPayment[] $staffPayments
 * @property Usage[] $warehouseUsages
 * @property Sale[] $warehouseSales
 */
class Staff extends \yii\db\ActiveRecord implements Linkable
{
    /**
     * Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const STATUS_FIRED = 2;

    /**
     * Gender
     */
    const GENDER_NONE = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * @param string $name
     * @param string $surname
     * @param string $phone
     * @param string $description
     * @param string $description_private
     * @param integer $gender
     * @param string $birth_date
     * @param string $iin
     * @param bool $has_calendar
     * @param string $color
     * @param string $code_1c
     * @return Staff
     */
    public static function add(
        $name,
        $surname,
        $phone,
        $description,
        $description_private,
        $gender,
        $birth_date,
        $iin,
        $has_calendar,
        $color,
        $code_1c
    ): Staff {
        $model                      = new Staff();
        $model->name                = $name;
        $model->surname             = $surname;
        $model->description         = $description;
        $model->gender              = $gender;
        $model->birth_date          = $birth_date;
        $model->iin                 = $iin;
        $model->phone               = $phone;
        $model->has_calendar        = $has_calendar;
        $model->description_private = $description_private;
        $model->color               = $color;
        $model->image_id            = null;
        $model->document_scan_id    = 1;
        $model->status              = self::STATUS_ENABLED;
        $model->payment_date        = null;
        $model->code_1c = $code_1c;

        return $model;
    }

    /**
     * @param string $name
     * @param string $surname
     * @param string $description
     * @param string $description_private
     * @param integer $gender
     * @param string $birth_date
     * @param string $iin
     * @param bool $has_calendar
     * @param string $color
     * @param string $code_1c
     */
    public function edit(
        $name,
        $surname,
        $description,
        $description_private,
        $gender,
        $birth_date,
        $iin,
        $has_calendar,
        $color,
        $code_1c
    ) {
        $this->name                = $name;
        $this->surname             = $surname;
        $this->description         = $description;
        $this->description_private = $description_private;
        $this->gender              = $gender;
        $this->birth_date          = $birth_date;
        $this->iin                 = $iin;
        $this->has_calendar        = $has_calendar;
        $this->color               = $color;
        $this->code_1c = $code_1c;
    }

    /**
     * @param string $phone
     */
    public function changePhone($phone)
    {
        self::guardPhoneNumber($phone);

        $this->phone = $phone;
    }

    /**
     * @param Image $image
     */
    public function setAvatar(Image $image)
    {
        $this->image = $image;
    }

    /**
     * @param Division[] $divisions
     */
    public function setStaffDivisions($divisions)
    {
        $this->divisions = $divisions;
    }

    /**
     * @param DivisionService[] $divisionServices
     */
    public function setStaffServices($divisionServices)
    {
        $this->divisionServices = $divisionServices;
    }

    /**
     * @param CompanyPosition[] $companyPositions
     */
    public function setWorkingPositions($companyPositions)
    {
        $this->companyPositions = $companyPositions;
    }

    /**
     * @param User $user
     * @param bool $see_own_orders
     * @param bool $can_create_order
     * @param bool $see_customer_phones
     * @param bool $can_update_order
     */
    public function grantSystemAccess(
        User $user,
        bool $see_own_orders,
        bool $can_create_order,
        bool $see_customer_phones,
        bool $can_update_order
    ) {
        $this->user                 = $user;
        $this->see_own_orders       = $see_own_orders;
        $this->create_order         = $can_create_order;
        $this->see_customer_phones  = $see_customer_phones;
        $this->can_update_order  = $can_update_order;
    }

    /**
     * Returns whether staff has access to system
     *
     * @return bool
     */
    public function hasUserPermissions()
    {
        return $this->user_id !== null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staffs}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Full Name'),
            'surname' => Yii::t('app', 'Staff Surname'),
            'status' => Yii::t('app', 'Status'),
            'phone' => Yii::t('app', 'Phone'),
            'description' => Yii::t('app', 'Description'),
            'description_private' => Yii::t('app', 'Description Private'),
            'birth_date' => Yii::t('app', 'Birth Date'),
            'image_id' => Yii::t('app', 'Image ID'),
            'document_scan_id' => Yii::t('app', 'Document Scan ID'),
            'services' => Yii::t('app', 'Services'),
            'has_calendar' => Yii::t('app', 'Staff has calendar'),
            'color'            => Yii::t('app', 'Staff color'),
            'gender'           => Yii::t('app', 'Gender'),
            'iin'              => Yii::t('app', 'IIN'),
            'user_permissions' => Yii::t('app', 'Staff Permissions'),
            'user_divisions'   => Yii::t('app', 'Staff Divisions'),
            'create_user'      => Yii::t('app', 'Give access to system'),
            'see_own_orders'   => Yii::t('app', 'See only own orders'),
            'create_order'     => Yii::t('app', 'Create order permission'),
            'divisionIds'      => Yii::t('app', 'Division ID'),
            'code_1c'          => Yii::t('app', '1C Nomenclature code'),
            'can_update_order'          => Yii::t('app', 'Может редактировать прошлые записи'),
        ];
    }

    /**
     * @return StaffQuery
     */
    public static function find()
    {
        return new StaffQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffSchedules()
    {
        return $this->hasMany(StaffSchedule::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \core\models\company\query\CompanyPositionQuery
     */
    public function getCompanyPosition() // TODO left for api/v1 compatibility
    {
        /** @var CompanyPositionQuery $query */
        $query = $this->hasOne(CompanyPosition::className(), ['id' => 'company_position_id'])
            ->viaTable('{{%staff_company_position_map}}', ['staff_id' => 'id']);
        return $query->notDeleted();
    }

    /**
     * @return \core\models\company\query\CompanyPositionQuery
     */
    public function getCompanyPositions()
    {
        /** @var CompanyPositionQuery $query */
        $query = $this->hasMany(CompanyPosition::className(), ['id' => 'company_position_id'])
            ->viaTable('{{%staff_company_position_map}}', ['staff_id' => 'id']);
        return $query->notDeleted();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCashflows()
    {
        return $this->hasOne(CompanyCashflow::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDivisions()
    {
        return $this->hasMany(UserDivision::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentScan()
    {
        return $this->hasOne(Image::className(), ['id' => 'document_scan_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|\core\models\order\query\OrderQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffReviews()
    {
        return $this->hasMany(StaffReview::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionServices()
    {
        return $this->hasMany(DivisionService::className(), ['id' => 'division_service_id'])
            ->viaTable('{{%staff_division_service_map}}', ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffPayrolls()
    {
        return $this->hasMany(PayrollStaff::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffPayments()
    {
        return $this->hasMany(StaffPayment::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseUsages()
    {
        return $this->hasMany(Usage::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseSales()
    {
        return $this->hasMany(Sale::className(), ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|\core\models\division\query\DivisionQuery
     */
    public function getDivisions()
    {
        return $this->hasMany(Division::className(), ['id' => 'division_id'])
                    ->viaTable('{{%staff_division_map}}', ['staff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|\core\models\query\ScheduleTemplateQuery
     */
    public function getScheduleTemplates()
    {
        return $this->hasMany(ScheduleTemplate::class, ['staff_id' => 'id']);
    }

    /**
     * Returns list of staffs working in company
     * @return array
     */
    public static function getOwnCompanyStaffList()
    {
        $result = [];
        $staffs = self::find()->company(false)->permitted()->enabled()->orderBy('surname, name')->all();
        foreach ($staffs as $staff) {
            /* @var Staff $staff */
            $result[$staff->id] = $staff->getFullName();
        }
        return $result;
    }

    /**
     * Returns list of orders related to staff
     * @param string $from start datetime
     * @param string $to end datetime
     * @param boolean $remove_disabled whether remove disabled orders
     * @param bool $remove_paid whether remove paid orders
     * @return OrderQuery
     */
    public function getStaffOrders($from = null, $to = null, $remove_disabled = false, $remove_paid = false)
    {
        $query = Order::find()
//            ->joinWith('divisionService')
            ->where(["staff_id" => $this->id]);
//            ->andWhere(['crm_division_services.status' => DivisionService::STATUS_ENABLED]);

        if ($from !== null) {
            $query->andWhere(':from <= datetime', [':from' => $from]);
        }

        if ($to !== null) {
            $query->andWhere('datetime <= :to', [":to" => $to]);
        }

        if ($remove_disabled == true) {
            $query->finished();
        }

        if ($remove_paid == true) {
            $query->andWhere(['crm_orders.is_paid' => false]);
        }

        return $query;
    }

    /**
     * Returns staff full name
     *
     * @return string
     */
    public function getFullName()
    {
        $staff_name = trim($this->surname . ' ' . $this->name);
        if (!$this->isEnabled()) {
            $staff_name .= ' (удалён)';
        }
        return $staff_name;
    }

    /**
     * Returns total value
     * @return double
     */
    public function getReviewValue()
    {
        $reviewsCount = $this->getStaffReviews()->count();
        $reviewsSum = $this->getStaffReviews()->sum('value');
        try {
            $reviewsAverage = $reviewsSum / $reviewsCount;
        } catch (\Exception $e) {
            $reviewsAverage = StaffReview::REVIEW_AVERAGE;
        }
        $reviewValue = ($reviewsCount / ($reviewsCount + StaffReview::REVIEW_LIMIT)) * $reviewsAverage +
            (StaffReview::REVIEW_LIMIT / ($reviewsCount + StaffReview::REVIEW_LIMIT)) * StaffReview::REVIEW_AVERAGE;
        return number_format($reviewValue, 1);
    }

    /**
     * @TODO Refactor
     * Fire staff
     * @return boolean
     */
    public function fire()
    {
        $this->status = Staff::STATUS_FIRED;
        $this->has_calendar = false;
        if ($this->user_id !== null) {
            $this->user->disable();
            $this->user_id = null;
        }
        return $this->save(false);
    }

    public function removeUserPermissions()
    {
        if ($this->hasUserPermissions()) {
            $this->user->disable();
            $this->user_id = null;
        }
    }

    /**
     * Enables staff
     */
    public function restore()
    {
        $this->status = Staff::STATUS_ENABLED;
    }

    /**
     * Returns if staff has schedule at date
     * @param Datetime $start_date
     * @param Datetime $end_date
     * @param Staff[] $staffs
     * @return array
     */
    public static function getScheduleAt(DateTime $start_date, DateTime $end_date, array $staffs)
    {
        $start_date = new \DateTime($start_date->format('Y-m-d'));
        $dates = [];
        while ($start_date <= $end_date) {
            foreach ($staffs as $staff) {
                foreach ($staff->divisions as $division) {
                    $dates[$staff->id][$division->id][$start_date->format('Y-m-d')]
                        = $staff->getDateScheduleAt($division->id, $start_date);
                }
            }
            $start_date->modify("+1 day");
        }

        return $dates;
    }

    /**
     * @deprecated
     * Returns list of available time to serve customer
     *
     * @param DivisionService $division_service
     * @param DateTime        $start_time
     * @param DateTime        $finish_time
     * @param integer         $interval
     *
     * @return array
     */
    public function getAvailableSchedule(
        DivisionService $division_service,
        DateTime $start_time,
        DateTime $finish_time,
        $interval = null
    ) {
        $company       = $division_service->divisions[0]->company;
        $time_interval = $interval ?: $company->interval;
        $result        = [];
        while ($start_time <= $finish_time) {
            $end_time = (clone $start_time);
            $end_time->modify("+{$division_service->average_time} minutes");
            try {
                $staff_time_available = StaffSchedule::isTimeAvailable(
                    $start_time,
                    $end_time,
                    $this
                );
                $company_time_available
                                      = $company->isOnlineAvailable($start_time);

                if ($staff_time_available && $company_time_available) {
                    $result[] = $start_time->format("Y-m-d H:i:s");
                }
            } catch (NotFoundException $e) {
                // Schedule not exists for given time
            }
            $start_time->modify("+{$time_interval} minutes");
        }

        return $result;
    }

    /**
     * @deprecated
     * @param integer $duration
     * @param Datetime $start_time
     * @param Datetime $finish_time
     * @return array
     */
    public function getAvailableDates($duration, DateTime $start_time, DateTime $finish_time)
    {
        $time_interval = Yii::$app->params['scheduleInterval'];

        $result = [];
        while ($start_time <= $finish_time) {

            /* @var StaffSchedule $schedule */
            $schedule = StaffSchedule::find()
                ->where([
                    'staff_id' => $this->id
                ])
                ->andWhere(['>=', 'start_at', $start_time->format('Y-m-d 00:00:00')])
                ->andWhere(['<=', 'end_at', $start_time->format('Y-m-d 24:00:00')])
                ->one();

            $start_time->modify('tomorrow');

            if (!$schedule) {
                continue;
            }

            /* @var Order[] $orders */
            $orders = Order::find()
                ->where(['staff_id' => $this->id])
                ->andWhere(['>=', 'datetime', $schedule->start_at])
                ->andWhere(['<=', 'datetime', $schedule->end_at])
                ->orderBy('datetime, duration DESC')
                ->all();

            $datetime = [];
            foreach ($orders as $order) {
                $intervals = $order->duration / $time_interval;
                $order_start_time = new DateTime($order->datetime);
                for ($i = $intervals; $i > 0; $i--) {
                    $datetime[$order_start_time->format('Y-m-d H:i:s')] = 1;
                    $order_start_time->modify("+{$time_interval} minutes");
                }
            }

            $startWork = new DateTime($schedule->start_at);
            $endWork = (new DateTime($schedule->end_at));
            $free_time = 0;
            while ($startWork <= $endWork) {
                if (isset($datetime[$startWork->format('Y-m-d H:i:s')])) {
                    $free_time = 0;
                } else {
                    $free_time += $time_interval;
                    if ($free_time == $duration) {
                        $result[] = $startWork->format('Y-m-d H:i:s');
                    }
                }
                $startWork->modify("+{$time_interval} minutes");
            }
        }

        return $result;
    }

    /**
     * Returns day schedule
     *
     * @param integer  $division_id
     * @param DateTime $date
     *
     * @return mixed
     */
    public function getDateScheduleAt(int $division_id, DateTime $date)
    {
        $cache = Yii::$app->cache;
        $cacheKey = StaffHelper::getScheduleCacheKey($division_id, $date);

        $staffSchedules = $cache->getOrSet($cacheKey, function () use($division_id, $date) {
            $tempSchedules = StaffSchedule::find()
                ->between($date->format('Y-m-d 00:00:00'), $date->format('Y-m-d 24:00:00'))
                ->division($division_id)
                ->all();

            return ArrayHelper::index($tempSchedules, function (StaffSchedule $staffSchedule){
                return $staffSchedule->staff_id;
            });
        });

        return array_key_exists($this->id, $staffSchedules) ? $staffSchedules[$this->id] : null;
    }

    /**
     * Get schedule available at this moment
     */
    public function getScheduleForNow()
    {
        $now = date('Y-m-d H:i:s');
        return StaffSchedule::find()
            ->where(['staff_id' => $this->id])
            ->andWhere(['<=', 'start_at', $now])
            ->andWhere(['>=', 'end_at', $now])
            ->one();
    }

    /**
     * @param int $division_id
     * @param Datetime $date
     *
     * @return void
     */
    public static function invalidateDateSchedule($division_id, DateTime $date)
    {
        $cacheKey = StaffHelper::getScheduleCacheKey($division_id, $date);
        Yii::$app->cache->delete($cacheKey);
    }

    /**
     * Create staff schedule for date
     * @param DateTime $date
     * @param $start
     * @param $end
     * @return bool
     * @throws Exception
     */
    public function createDateSchedule(DateTime $date, $start, $end)
    {
        $start_date = new DateTime($date->format('Y-m-d ' . $start));
        $finish_date = new DateTime($date->format('Y-m-d ' . $end));

        $transaction = Yii::$app->db->beginTransaction();
        try {
            while ($start_date < $finish_date) {
                $schedule = StaffSchedule::find()->where([
                    'datetime' => $start_date->format('Y-m-d H:i:s'),
                    'staff_id' => $this->id
                ])->one();

                if (!$schedule) {
                    $schedule = new StaffSchedule([
                        'datetime' => $start_date->format('Y-m-d H:i:s'),
                        'staff_id' => $this->id,
                        'elapsed_time' => Yii::$app->params['scheduleInterval'],
                    ]);
                    if (!$schedule->save()) {
                        throw new Exception(Json::encode($schedule->getErrors()));
                    }
                }
                $start_date->modify("+" . Yii::$app->params['scheduleInterval'] . " minutes");
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }

    /**
     * Removes schedule at date
     * @param DateTime $date
     * @return bool
     */
    public function deleteDateSchedule(DateTime $date)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orders = StaffSchedule::find()
                ->where(
                    'order_id IS NOT NULL AND staff_id = :staff_id AND '
                    . ' :yesterday <= datetime AND datetime < :tomorrow', [
                    ':staff_id' => $this->id,
                    ':yesterday' => $date->format('Y-m-d 00:00:00'),
                    ':tomorrow' => $date->format('Y-m-d 23:59:59'),
                ])->count();
            if ($orders > 0) {
                throw new \Exception(Yii::t('app', 'Has schedule with order'));
            }

            StaffSchedule::deleteAll(
                'order_id IS NULL AND staff_id = :staff_id AND '
                . ' :yesterday <= datetime AND datetime < :tomorrow',
                [
                    ':staff_id' => $this->id,
                    ':yesterday' => $date->format('Y-m-d 00:00:00'),
                    ':tomorrow' => $date->format('Y-m-d 23:59:59'),
                ]
            );
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('id', Json::encode($e->getMessage()));
            return false;
        }
        return true;
    }

    /**
     * Returns plain phone number
     *
     * @return string
     */
    public function getPlainPhone(): string
    {
        return str_replace(' ', '', $this->phone);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'surname'              => function () {
                return trim($this->surname);
            },
            'fullname' => 'fullName',
            'phone',
            'description',
            'description_private',
            'gender',
            'gender_name'          => function () {
                return GenderHelper::getGenderLabel($this->gender);
            },
            'birth_date',
            'has_calendar',
            'color',
            'see_own_orders',
            'can_create_order'     => 'create_order',
            'has_user_permissions' => function () {
                return $this->hasUserPermissions();
            },
            'image'                => 'avatarImageUrl',
            'code_1c',
            'can_update_order',
        ];
    }

    public function extraFields()
    {
        return [
            'divisions',
            'rating'           => 'reviewValue',
            'position'         => 'companyPosition',
            // TODO возможно сериализация используется в api/v1
            'positions'        => 'companyPositions',
            'reviews'          => 'staffReviews',
            'services'         => function () {
                return $this->getDivisions()->select('id')->column();
            },
            'user_divisions'   => function () {
                if ( ! $this->hasUserPermissions()) {
                    return [];
                }

                return ArrayHelper::getColumn($this->userDivisions,
                    'division_id');
            },
            'user_permissions' => function () {
                if ( ! $this->hasUserPermissions()) {
                    return [];
                }
                $permissions = \Yii::$app->authManager->getPermissionsByUser($this->user_id);

                return ArrayHelper::getColumn($permissions, 'name');
            }
        ];
    }

    public function getLinks()
    {
        return [
            'self' => Url::to(['/v2/staff/default/view', 'id' => $this->id], true),
        ];
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => function() { return date('Y-m-d H:i:s'); }
            ],
            'saveRelations' => [
                'class' => SaveRelationsBehavior::className(),
                'relations' => ['user', 'divisions', 'divisionServices', 'companyPositions', 'image'],
            ],
            \common\components\HistoryBehavior::className(),
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @return \core\models\order\query\OrderQuery
     */
    public function getForthcomingOrders()
    {
        return $this->hasMany(Order::class, ['staff_id' => 'id'])->startFrom(new \DateTime(), false)->enabled();
    }

    /**
     * @return bool
     */
    public function hasForthcomingOrders()
    {
        return $this->getForthcomingOrders()->exists();
    }

    /**
     * @param int $division_id
     * @return bool
     */
    public function hasScheduleTemplate(int $division_id)
    {
        return $this->getScheduleTemplates()->byDivision($division_id)->exists();
    }

    /**
     * @param int $division_id
     * @param bool $as_array
     * @return array|ScheduleTemplate|null
     */
    public function getScheduleTemplate(int $division_id, bool $as_array = false)
    {
        return $this->getScheduleTemplates()->byDivision($division_id)->asArray($as_array)->one();
    }

    /**
     * Returns customer image path
     *
     * @return string
     */
    public function getAvatarImageUrl()
    {
        if ( ! $this->image_id) {
            return Image::getImageUrlTo(
                Yii::$app->params['staffDefaultImageId'],
                Image::SIZE_AVATAR
            );
        }

        $image = Image::findOne($this->image_id);
        if ( ! $image) {
            throw new NotFoundException('Model not found');
        }

        return $image->getAvatarImageUrl();
    }

    public static function map()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->permitted()->all(), 'id', 'fullName');
    }

    public function canSeeCustomerPhones()
    {
        return $this->see_customer_phones == true;
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    private static function guardPhoneNumber($phone)
    {
        $invalid_phone = !preg_match(CustomerHelper::PHONE_VALIDATE_PATTERN, $phone);
        if ($invalid_phone && $phone !== null) {
            throw new \DomainException('Phone is invalid');
        }
    }
}

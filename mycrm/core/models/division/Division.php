<?php

namespace core\models\division;

use common\components\HistoryBehavior;
use core\helpers\division\DivisionHelper;
use core\models\City;
use core\models\company\Company;
use core\models\customer\Customer;
use core\models\customer\CustomerFavourite;
use core\models\division\query\DivisionQuery;
use core\models\finance\CompanyCash;
use core\models\Image;
use core\models\order\Order;
use core\models\Payment;
use core\models\Service;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\user\User;
use DateTime;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%divisions}}".
 *
 * @property integer $id
 * @property string $address
 * @property integer $category_id
 * @property integer $city_id
 * @property integer $company_id
 * @property string $description
 * @property string $key
 * @property double $latitude
 * @property double $longitude
 * @property string $name
 * @property double $rating
 * @property integer $status
 * @property integer $logo_id
 * @property integer $default_notification_time
 * @property string $url
 * @property string $working_finish
 * @property string $working_start
 *
 * @property CustomerFavourite[] $customerFavourites
 * @property Order[] $orders
 * @property DivisionImage[] $divisionImages
 * @property DivisionPayment[] $divisionPayments
 * @property DivisionPhone[] $divisionPhones
 * @property DivisionReview[] $divisionReviews
 * @property DivisionService[] $divisionServices
 * @property DivisionSettings $settings
 * @property DivisionSocial[] $divisionSocials
 * @property Payment[] $payments
 * @property Image $logo
 * @property City $city
 * @property Company $company
 * @property Staff[] $staffs
 * @property User[] $users
 */
class Division extends \yii\db\ActiveRecord
{
    /**
     * Statuses
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED_NAME = "disabled";
    const STATUS_ENABLED_NAME  = "enabled";

    /**
     * Working statuses
     */
    const WORK_CLOSED = 0;
    const WORK_OPEN = 1;

    /**
     * Categories
     */
    const CATEGORY_BEAUTY = 2;

    /**
     * Additional properties
     */
    public $country_id;
    public $imageFiles;
    public $phones = [];

    /**
     * @param string $address
     * @param int    $category_id
     * @param int    $company_id
     * @param int    $city_id
     * @param string $description
     * @param float  $latitude
     * @param float  $longitude
     * @param string $name
     * @param int    $status
     * @param string $url
     * @param string $working_finish
     * @param string $working_start
     * @param int    $default_notification_time
     *
     * @return Division
     * @throws \yii\base\Exception
     */
    public static function add(
        string $address,
        int $category_id,
        int $company_id,
        int $city_id,
        string $description,
        float $latitude,
        float $longitude,
        string $name,
        int $status,
        string $url,
        string $working_finish,
        string $working_start,
        int $default_notification_time
    ) {
        $division = new self();
        $division->address = $address;
        $division->category_id = $category_id;
        $division->company_id = $company_id;
        $division->city_id = $city_id;
        $division->description = $description;
        $division->latitude = $latitude;
        $division->longitude = $longitude;
        $division->name = $name;
        $division->status = $status;
        $division->url = $url;
        $division->working_finish = $working_finish;
        $division->working_start = $working_start;
        $division->default_notification_time = $default_notification_time;

        $division->key = Yii::$app->security->generateRandomString(12);
        $division->rating = DivisionReview::getReviewValue($division);

        return $division;
    }

    /**
     * @param string $address
     * @param int    $category_id
     * @param int    $company_id
     * @param int    $city_id
     * @param string $description
     * @param float  $latitude
     * @param float  $longitude
     * @param string $name
     * @param int    $status
     * @param string $url
     * @param string $working_finish
     * @param string $working_start
     * @param int    $default_notification_time
     */
    public function edit(
        string $address,
        int $category_id,
        int $company_id,
        int $city_id,
        string $description,
        float $latitude,
        float $longitude,
        string $name,
        int $status,
        string $url,
        string $working_finish,
        string $working_start,
        int $default_notification_time
    ) {
        $this->address = $address;
        $this->category_id = $category_id;
        $this->company_id = $company_id;
        $this->city_id = $city_id;
        $this->description = $description;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->name = $name;
        $this->status = $status;
        $this->url = $url;
        $this->working_finish = $working_finish;
        $this->working_start = $working_start;
        $this->default_notification_time = $default_notification_time;
    }

    /**
     * @param int $image_id
     */
    public function changeLogo(int $image_id)
    {
        $this->logo_id = $image_id;
    }

    /**
     * Disable division
     */
    public function disable()
    {
        $this->status = self::STATUS_DISABLED;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%divisions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'city_id', 'latitude', 'company_id', 'category_id',
                'longitude', 'working_start', 'working_finish', 'name'], 'required'],
            [['company_id', 'city_id', 'status', 'category_id', 'default_notification_time'], 'integer'],
            [['imageFiles'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 12],
            [['rating', 'latitude', 'longitude'], 'number'],
            [['working_start', 'working_finish'], 'safe'],
            ['working_finish', 'compare', 'compareAttribute' => 'working_start', 'operator' => '>'],
            [['name', 'url', 'address', 'phone'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_ENABLED],
            [['description'], 'string', 'max' => 500],
            ['phones', 'each', 'rule' => ['match', 'pattern' => '/^\+[0-9]{1} [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}$/i']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Url'),
            'address' => Yii::t('app', 'Address'),
            'company_id' => Yii::t('app', 'Company ID'),
            'city_id' => Yii::t('app', 'City ID'),
            'payments' => Yii::t('app', 'Payments'),
            'country_id' => Yii::t('app', 'Country ID'),
            'status' => Yii::t('app', 'Status'),
            'rating' => Yii::t('app', 'Rating'),
            'latitude' => Yii::t('app', 'Position Latitude'),
            'longitude' => Yii::t('app', 'Position Longitude'),
            'working_start' => Yii::t('app', 'Working Start'),
            'working_finish' => Yii::t('app', 'Working Finish'),
            'phone' => Yii::t('app', 'Phone'),
            'phones' => Yii::t('app', 'Phone'),
            'description' => Yii::t('app', 'Description'),
            'category_id' => Yii::t('app', 'Division Type'),
            'default_notification_time' => Yii::t('app', 'Default notification time'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionImages()
    {
        return $this->hasMany(DivisionImage::className(), ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionPayments()
    {
        return $this->hasMany(DivisionPayment::className(), ['division_id' => 'id'])
            ->andWhere(["{{%division_payments}}.status" => DivisionPayment::STATUS_ENABLED])
            ->orderBy(["payment_id" => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionPhones()
    {
        return $this->hasMany(DivisionPhone::className(), ['division_id' => 'id'])
            ->where(["{{%division_phones}}.status" => DivisionPhone::STATUS_ENABLED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionSocials()
    {
        return $this->hasMany(DivisionSocial::className(), ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogo()
    {
        return $this->hasOne(Image::className(), ['id' => 'logo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCash()
    {
        return $this->hasOne(CompanyCash::className(), ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffs()
    {
        return $this->hasMany(Staff::className(), ['id' => 'staff_id'])
                    ->viaTable('{{%staff_division_map}}', ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::className(), ['id' => 'payment_id'])
            ->via('divisionPayments', function (\yii\db\ActiveQuery $query) {
                return $query->andWhere(["{{%division_payments}}.status" => DivisionPayment::STATUS_ENABLED]);
            });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionReviews()
    {
        return $this->hasMany(DivisionReview::className(), ['division_id' => 'id'])->orderBy("id");
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionServices()
    {
        return $this->hasMany(DivisionService::className(), ['id' => 'division_service_id'])
            ->viaTable('{{%service_division_map}}', ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules()
    {
        return $this->hasMany(DivisionSchedule::className(), ['division_id' => 'id'])->orderBy('day_num ASC, from ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettings()
    {
        return $this->hasOne(DivisionSettings::className(), ['division_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->via('staffs');
    }

    /**
     * Returns list for drop down list
     * @return array [ 1 => 'Name (Address)', 2 => 'Name (Address)' ]
     */
    public static function getOwnCompanyDivisionsList()
    {
        $models = Division::find()->company()->permitted()->enabled()->orderBy('name ASC')->all();
        $result = [];
        foreach ($models as $model)
        {
            /* @var Division $model */
            $result[$model->id] = $model->getTotalName();
        }
        return $result;
    }

    public static function getCompanyDivisionsList(Company $company)
    {
        $models = Division::find()->company($company->id)->enabled()->orderBy('name ASC')->all();

        return ArrayHelper::map($models, 'id', 'totalName');
    }

    /**
     * Returns list for drop down list
     * @return array [ 1 => 'Name', 2 => 'Name' ]
     */
    public static function getOwnDivisionsNameList()
    {
        $models = Division::find()->company()->permitted()->enabled()->all();
        $result = [];
        foreach ($models as $model)
        {
            /* @var Division $model */
            $result[$model->id] = $model->name;
        }
        return $result;
    }

    /**
     * Returns weather is open
     * @return boolean
     */
    public function isOpen()
    {
        $now = new DateTime();
        $start = new DateTime($this->working_start);
        $finish = new DateTime($this->working_finish);
        if($finish > $start)
        {
            if($start <= $now && $now <= $finish)
            {
                return self::WORK_OPEN;
            }
        }
        else
        {
            if(($start <= $now && $now <= (new DateTime('23:59:59')))
                || ((new DateTime('00:00:00')) <= $now && $now <= $finish))
            {
                return self::WORK_OPEN;
            }
        }
        return self::WORK_CLOSED;
    }

    /**
     * If division category does not apply to beauty
     * it can print orders
     */
    public function canPrintOrder()
    {
        return true || $this->category_id != self::CATEGORY_BEAUTY;
    }

    /**
     * Weather division is favourite to customer
     * @param Customer $customer
     * @return boolean
     */
    public function isFavourite(Customer $customer)
    {
        return (CustomerFavourite::findOne([
            "customer_id" => $customer->id, "division_id" => $this->id
        ])) != null;
    }

    /**
     * Returns number of orders customer has ordered in division
     * @param Customer $customer
     * @return boolean
     */
    public function countOrders(Customer $customer)
    {
        return Order::find()
            ->joinWith(["companyCustomer"])
            ->where([
                "{{%company_customers}}.customer_id" => $customer->id,
                "{{%orders}}.division_id" => $this->id
            ])->count();
    }

    /**
     * Upload images
     *
     * @param UploadedFile $file
     *
     * @return false|DivisionImage
     * @throws \yii\db\Exception
     */
    public function upload($file)
    {
        if (($image = Image::uploadImage($file)) !== null)
        {
            $divisionImage = new DivisionImage();
            $divisionImage->division_id = $this->id;
            $divisionImage->image_id = $image->id;
            if ($divisionImage->save())
                return $divisionImage;
        }
        return false;
    }

    /**
     * Returns list of working hours
     * @return array ["00:00", "00:30", "01:00"]
     */
    public function getWorkingTimeList()
    {
        $start_time = new DateTime($this->working_start);
        $finish_time = new DateTime($this->working_finish);
        $time_interval = Yii::$app->params['scheduleInterval'];
        $result = [];
        while ($start_time <= $finish_time) {
            $result[$start_time->format("H")] = $start_time->format("H:00");
            $start_time->modify("+{$time_interval} minutes");
        }
        return $result;
    }

    /**
     * Returns division information
     * @param ServiceCategory|null $selected_service_category
     * @return array
     */
    public function getInformation(ServiceCategory $selected_service_category = null)
    {
        $categories = [];
        $service_min_price = null;
        foreach($this->divisionServices as $division_service_model)
        {
            foreach ($division_service_model->categories as $category)
            {
                $categories[$category->id][$division_service_model->id] = [
                    'id'            => $division_service_model->id,
                    'name'          => $division_service_model->service_name,
                    'price'         => $division_service_model->price,
                    'price_max'     => $division_service_model->price_max,
                    'duration'      => $division_service_model->average_time,
                    'description'   => $division_service_model->description,
                    'category_id'   => $category->id,
                    'category_name' => $category->name,
                ];

                if ($selected_service_category !== null && $category->id == $selected_service_category->id)
                {
                    $service_min_price = ($service_min_price == null) ?
                        $division_service_model->price : min($service_min_price, $division_service_model->price);
                }
            }
        }

        $services_list = [];
        foreach ($categories as $category_id => $division_services)
        {
            $category_name = "";
            $temporary_services = [];
            foreach ($division_services as $service)
            {
                $category_name = $service['category_name'];
                $temporary_services[] = [
                    'id' => $service['id'],
                    'name' => $service['name'],
                    'price' => $service['price'],
                    'price_max' => $service['price_max'],
                    'duration' => $service['duration'],
                    'description' => $service['description'],
                ];
            }

            $selected_category_id = $selected_service_category ? $selected_service_category->id : null;
            $index = ($category_id == $selected_category_id) ? 0 : $category_id;

            $var = [
                'category_name' => $category_name,
                'category_id' => $category_id,
                'division_services' => $temporary_services,
            ];

            if ($index !== 0)
            {
                array_push($services_list, $var);
            }
            else
            {
                array_unshift($services_list, $var);
            }
        }

        $services = [];
        foreach ($services_list as $service)
        {
            $services[] = $service;
        }

        $reviews = [];
        $reviews_model = $this->divisionReviews;
        foreach($reviews_model as $review)
        {
            $reviews[] = [
                'name' => $review->customer->name,
                'image' => $review->customer->getAvatarImageUrl(),
                'value' => $review->value,
                'comment' => $review->comment,
                'datetime' => $review->created_time,
            ];
        }

        $payments = [];
        $division_payments_model = $this->divisionPayments;
        foreach($division_payments_model as $payment)
        {
            $payments[] = [
                'id' => $payment->payment_id,
                'name' => Yii::t('app', $payment->payment->name),
            ];
        }

        $images = [];
        $division_images_model = $this->divisionImages;
        $image_logo = \Yii::$app->params['api_host'] . $this->company->logo->getPath();
        foreach($division_images_model as $division_image)
        {
            $image_logo = \Yii::$app->params['api_host'] . $division_image->image->getPath();
            /* @var DivisionImage $division_image */
            $images[] = [
                'path' => $image_logo,
            ];
        }

        $data = [
            'id' => $this->id,
            'address' => $this->address,
            'rating' => $this->rating,
            'name' => $this->name,
            'description' => $this->description,
            'service_min_price' => $service_min_price,
            'image' => $image_logo,
            'position_latitude' => $this->latitude,
            'position_longitude' => $this->longitude,
            'is_open' => $this->isOpen(),
            'working_start' => $this->working_start,
            'working_finish' => $this->working_finish,
            'phones' => [],
            'email' => '',
            'url' => $this->url,
            'services' => $services,
            'reviews' => $reviews,
            'payments' => $payments,
            'images' => $images,
            'category_id' => $this->category_id,
        ];
        $data['is_favourite'] = false;
        $data['can_review'] = false;
        if (!Yii::$app->user->isGuest)
        {
            $customer = \Yii::$app->user->identity;
            /* @var Customer $customer */
            $data['can_review'] = ($this->countOrders($customer)) > 0;
            $data['is_favourite'] = $this->isFavourite($customer);
        }

        return $data;
    }

    /**
     * @param null $division_id
     * @return array
     */
    public static function getAllPayments($division_id = null) {
        return ArrayHelper::map(Division::find()
            ->select(["payment_id AS id", "{{%payments}}.name"])
            ->distinct()
            ->joinWith('divisionPayments.payment', false)
            ->enabled()
            ->id($division_id)
            ->asArray()
            ->all(), "id", function (array $data) {
            return Yii::t('app', $data["name"]);
        }
        );
    }

    /**
     * Returns division name representation
     * @return string
     */
    public function getTotalName()
    {
        return "{$this->name} ({$this->address})";
    }

    public function fields()
    {
        $city = $this->city;
        return [
            'address'        => 'address',
            'category_id'    => 'category_id',
            'city_id'        => 'city_id',
            'city_name'      => function () use ($city) {
                return $city->name;
            },
            'country_id'     => function () use ($city) {
                return $city->country_id;
            },
            'country_name'   => function () use ($city) {
                return $city->country->name;
            },
            'company_id'     => 'company_id',
            "default_notification_time",
            'description'    => 'description',
            'id'             => 'id',
            'key'            => function() {
                return (string) $this->key;
            },
            'latitude'       => 'latitude',
            'longitude'      => 'longitude',
            'name'           => 'name',
            'phone'          => 'phone',
            'rating'         => 'rating',
            'status'         => 'status',
            'status_name'    => function () {
                return DivisionHelper::getStatusLabel($this->status);
            },
            'status_list'    => function () {
                return DivisionHelper::getStatuses();
            },
            'url'            => 'url',
            'working_finish' => 'working_finish',
            'working_start'  => 'working_start',
            'logo_path'      => function () {
                return $this->getLogoPath();
            },
        ];
    }

    /**
     * @return null|string
     */
    public function getLogoPath()
    {
        if (empty($this->logo_id)) {
            return $this->company->getLogoPath();
        }
        return \Yii::$app->params['api_host'] . $this->logo->getPath();
    }


    public function extraFields()
    {
        return [
            'phones'     => 'divisionPhones',
            'staffs'     => function (Division $model) {
                return $model->getStaffs()->enabled()->all();
            },
            'payments',
            'self-staff' => function (Division $model) {
                if (Yii::$app->user->isGuest) {
                    return null;
                }

                /* @var User $user */
                $user  = Yii::$app->user->identity;
                $staff = $user->staff;
                if ( ! $staff) {
                    return null;
                }

                return Staff::find()
                    ->division($model->id)
                    ->andWhere(['{{%staffs}}.id' => $staff->id])
                    ->one();
            },
            'settings',
            'company'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new DivisionQuery(get_called_class());
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
}

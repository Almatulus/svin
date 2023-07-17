<?php

namespace core\forms\division;

use core\helpers\DateHelper;
use core\models\division\Division;
use core\services\division\dto\DivisionData;
use Yii;
use yii\base\Model;

class DivisionCreateForm extends Model
{
    public $address;
    public $company_id;
    public $category_id;
    public $city_id;
    public $description;
    public $latitude;
    public $longitude;
    public $name;
    public $status;
    public $url;
    public $working_finish;
    public $working_start;
    public $default_notification_time = 0;

    public $logo_id;
    public $image_file;

    public $country_id;
    public $payments = [];
    public $imageFiles;
    public $phones = [];

    public $notification_time_before_lunch;
    public $notification_time_after_lunch;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->company_id = Yii::$app->user->getIdentity()->company_id;
        $this->latitude = "43.23";
        $this->longitude = "76.91";
        $this->phones = [""];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'city_id', 'company_id', 'category_id', 'latitude',
                'longitude', 'name', 'payments', 'working_finish', 'working_start', 'default_notification_time'], 'required'],

            [['company_id', 'city_id', 'status', 'category_id'], 'integer'],
            [['default_notification_time'], 'integer', 'min' => 0],

            [['company_id', 'city_id', 'status', 'category_id'], 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],
            [['latitude', 'longitude'], 'number'],
            [['working_start', 'working_finish'], 'safe'],
            [['name', 'url', 'address'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],

            ['company_id', 'default', 'value' => \Yii::$app->user->getIdentity()->company_id],
            ['latitude', 'default', 'value' => "43.23"],
            ['longitude', 'default', 'value' => "76.91"],
            ['status', 'default', 'value' => Division::STATUS_ENABLED],

            [['working_finish', 'working_start'], 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],
            ['working_finish', 'compare', 'compareAttribute' => 'working_start', 'operator' => '>'],

            [['payments'], 'each', 'rule' => ['integer']],
            [['imageFiles'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 12],
            ['phones', 'each', 'rule' => ['match', 'pattern' => '/^\+[0-9] [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}$/i']],

            [
                'notification_time_before_lunch',
                'required',
                'when'                   => function () {
                    return $this->notification_time_after_lunch;
                },
                'enableClientValidation' => false
            ],
            ['notification_time_before_lunch', 'time', 'format' => 'HH:mm'],
            ['notification_time_before_lunch', 'compare', 'compareValue' => '14:00', 'operator' => '>'],

            [
                'notification_time_after_lunch',
                'required',
                'when'                   => function () {
                    return $this->notification_time_before_lunch;
                },
                'enableClientValidation' => false
            ],
            ['notification_time_after_lunch', 'time', 'format' => 'HH:mm'],
            ['notification_time_after_lunch', 'compare', 'compareValue' => '14:00', 'operator' => '<'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'address'        => Yii::t('app', 'Address'),
            'category_id'    => Yii::t('app', 'Division Type'),
            'company_id'     => Yii::t('app', 'Company ID'),
            'country_id'     => Yii::t('app', 'Country ID'),
            'city_id'        => Yii::t('app', 'City ID'),
            'description'    => Yii::t('app', 'Description'),
            'latitude'       => Yii::t('app', 'Position Latitude'),
            'longitude'      => Yii::t('app', 'Position Longitude'),
            'name'           => Yii::t('app', 'Name'),
            'phones'         => Yii::t('app', 'Phone'),
            'payments'       => Yii::t('app', 'Payments'),
            'status'         => Yii::t('app', 'Status'),
            'url'            => Yii::t('app', 'Url'),
            'working_start'  => Yii::t('app', 'Working Start'),
            'working_finish' => Yii::t('app', 'Working Finish'),

            'default_notification_time' => Yii::t('app', 'Default notification time'),

            'notification_time_before_lunch' => Yii::t('app', 'Время уведомления для записей до обеда (сообщение отправится за день до записи)'),
            'notification_time_after_lunch'  => Yii::t('app', 'Время уведомлений для записей после обеда (сообщение отправится в день записи)'),
        ];
    }

    public function getDto()
    {
        return new DivisionData(
            $this->address,
            $this->category_id,
            $this->company_id,
            $this->city_id,
            $this->description,
            $this->latitude,
            $this->longitude,
            $this->name,
            $this->status,
            $this->url,
            $this->working_finish,
            $this->working_start,
            $this->default_notification_time,
            $this->logo_id
        );
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Division';
    }
}
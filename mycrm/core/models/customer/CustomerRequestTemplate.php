<?php

namespace core\models\customer;

use core\helpers\customer\RequestTemplateHelper;
use core\models\company\Company;
use Yii;

/**
 * This is the model class for table "crm_customer_request_templates".
 *
 * @property integer $id
 * @property string $key
 * @property boolean $is_enabled
 * @property string $template
 * @property integer $company_id
 * @property string $description
 * @property integer $quantity
 * @property integer $quantity_type
 * @property integer $type
 *
 * @property Company $company
 */
class CustomerRequestTemplate extends \yii\db\ActiveRecord
{
    const TYPE_VISIT_REMIND = 1;
    const TYPE_REQUEST_COMMENT_AFTER_VISIT_ONLINE = 2;
    const TYPE_REQUEST_COMMENT_AFTER_VISIT_OFFLINE = 3;
    const TYPE_REQUEST_WITH_RECORD_INFO = 4;
    const TYPE_NOTIFY_CLIENT_ABOUT_RECORD = 5;
    const TYPE_BIRTHDAY = 6;
    const TYPE_REQUEST_CONFIRM_VIA_INET = 7;
    const TYPE_NOTIFY_RECORD_REMOVAL = 8;
    const TYPE_NOTIFY_ADMIN_NEW_RECORD_INET = 9;
    const TYPE_NOTIFY_STAFF_NEW_RECORD_INET = 10;
    const TYPE_NOTIFY_ADMIN_REMOVE_RECORD_INET = 11;
    const TYPE_NOTIFY_STAFF_REMOVE_RECORD_INET = 12;
    const TYPE_NOTIFY_CLIENT_NEW_DISCOUNT = 13;
    const TYPE_NOTIFY_CLIENT_DISCOUNT_EXPIRE = 14;
    const TYPE_NOTIFY_LOW_BALANCE = 15;
    const TYPE_NOTIFY_STAFF_WORKTIME_EXPIRE = 16;
    const TYPE_NOTIFY_HEALTH_EXAMINATION = 18;
    const TYPE_NOTIFY_CUSTOMER_SINCE_LAST_VISIT = 17;

    public $description;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_customer_request_templates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'company_id'], 'required'],
            [
                'template',
                'required',
                'when'                   => function () {
                    return $this->is_enabled;
                },
                'enableClientValidation' => false
            ],
            [['is_enabled'], 'boolean'],
            [['company_id'], 'integer'],
            [['key'], 'string', 'max' => 127],
            [['template'], 'string', 'max' => 350],
            [
                ['key', 'company_id'],
                'unique',
                'targetAttribute' => ['key', 'company_id'],
                'message'         => 'The combination of Key and Company ID has already been taken.'
            ],

            [
                '!type',
                'default',
                'value' => function () {
                    return $this->isDelayedByDefault() ? RequestTemplateHelper::TYPE_DELAYED : RequestTemplateHelper::TYPE_DEFAULT;
                }
            ],
            ['!type', 'integer'],
            ['!type', 'in', 'range' => array_keys(RequestTemplateHelper::getTypes())],

            [
                'quantity_type',
                'required',
                'enableClientValidation' => false,
                'when'                   => function (self $model) {
                    return $model->isDelayedByDefault() && $model->is_enabled;
                }
            ],
            ['quantity_type', 'integer'],
            ['quantity_type', 'in', 'range' => array_keys(RequestTemplateHelper::getQuantityTypes())],

            [
                'quantity',
                'required',
                'enableClientValidation' => false,
                'when'                   => function (self $model) {
                    return $model->isDelayedByDefault() && $model->is_enabled;
                }
            ],
            ['quantity', 'integer', 'min' => 1],

            [
                'is_enabled',
                'filter',
                'filter' => function ($value) {
                    return $value ? boolval($value) : $value;
                }
            ],
            [
                'quantity',
                'filter',
                'filter' => function ($value) {
                    return $value ? intval($value) : $value;
                }
            ],
            [
                'quantity_type',
                'filter',
                'filter' => function ($value) {
                    return $value ? intval($value) : $value;
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'key'        => 'Key',
            'is_enabled' => 'Is Enabled',
            'quantity'   => Yii::t('app', 'Delay to'),
            'template'   => Yii::t('app', 'Template'),
            'company_id' => 'Company ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @param int|null $company_id
     * @return array
     */
    public static function loadTemplates(int $company_id = null)
    {
        $keys = [];
        $templates = self::getTemplateDefaultSettings();
        $excludedKeys = self::getExcludedTemplates();
        foreach ($templates as $key => $array) {
            if (!in_array($key, $excludedKeys)) {
                $keys[] = self::initSettings($key, $array[0], $array[1], $company_id);
            }
        }
        return $keys;
    }

    /**
     * @param $key
     * @param $company_id
     * @return CustomerRequestTemplate|null
     */
    public static function loadTemplate($key, $company_id)
    {
        $templates = self::getTemplateDefaultSettings();
        if (!isset($templates[$key])) {
            return null;
        }
        $template = $templates[$key];
        return self::initSettings($key, $template[0], $template[1], $company_id);
    }

    /**
     * @return array
     */
    public static function getTemplateDefaultSettings()
    {
        return [
            // Enabled
            self::TYPE_NOTIFY_CLIENT_ABOUT_RECORD          => [
                'Уведомление клиенту о записи через График (офлайн запись)',
                'Вы записаны в %DIVISION_NAME%. Услуга: %SERVICE_TITLE%, Специалист: %MASTER_NAME%, Время: %DATETIME%, %COMPANY_ADDRESS%, %CONTACT_PHONE%'
            ],
            self::TYPE_REQUEST_WITH_RECORD_INFO            => [
                'Уведомление клиенту о записи через приложения (онлайн запись)',
                'Вы записаны в %DIVISION_NAME% на услугу %SERVICE_TITLE% на %DATE% в %HOURMINUTES%, %COMPANY_ADDRESS%, %CONTACT_PHONE%'
            ],
            self::TYPE_NOTIFY_RECORD_REMOVAL               => [
                'Уведомление клиенту об удалении записи',
                'Отменена Ваша запись в %DIVISION_NAME%. Услуга: %SERVICE_TITLE%, Специалист: %MASTER_NAME%, Время: %DATETIME%'
            ],
            self::TYPE_NOTIFY_STAFF_NEW_RECORD_INET        => [
                'Уведомление мастеру о записи',
                'Новая запись: %CLIENT_NAME%; Услуга: %SERVICE_TITLE% на %DATETIME%'
            ],
            self::TYPE_BIRTHDAY                            => [
                'Поздравление клиенту с Днём Рождения',
                'Поздравляем Вас с Днём Рождения! Желаем всех благ и хорошего настроения! С Уважением, %COMPANY_NAME%'
            ],
            self::TYPE_NOTIFY_CLIENT_NEW_DISCOUNT          => [
                'Уведомление клиенту о новой скидке',
                '%CLIENT_NAME%, Вам присвоена скидка %DISCOUNT%% в %COMPANY_NAME%'
            ],
            self::TYPE_NOTIFY_CLIENT_DISCOUNT_EXPIRE       => [
                'Предупреждение клиента об окончании действия скидки',
                '%CLIENT_NAME%, Ваша скидка в %COMPANY_NAME% сократилась до %DISCOUNT%%'
            ],

            // Disabled
            self::TYPE_NOTIFY_ADMIN_NEW_RECORD_INET        => [
                'Уведомление администратору о записи через приложения (онлайн запись) *',
                'Новая запись: %CLIENT_NAME% (%CLIENT_PHONE%); Услуга: %SERVICE_TITLE%; Сотрудник: %MASTER_NAME% на %DATETIME%'
            ],
            self::TYPE_REQUEST_COMMENT_AFTER_VISIT_ONLINE  => [
                'Запрос отзыва после визита (онлайн запись) *',
                'Сегодня вы были записаны в %COMPANY_NAME%. Оставить отзыв можно, перейдя по ссылке: %LINK%'
            ],
            self::TYPE_REQUEST_COMMENT_AFTER_VISIT_OFFLINE => [
                'Запрос отзыва после визита (офлайн запись) *',
                'Сегодня вы были записаны в %COMPANY_NAME%. Оставить отзыв можно, перейдя по ссылке: %LINK%'
            ],

            // Hidden
            self::TYPE_NOTIFY_ADMIN_REMOVE_RECORD_INET     => [
                'Уведомление администратору об удалении записи через интернет *',
                'Заказ №%ORDER_KEY%. Клиент: %CLIENT_NAME% (%CLIENT_PHONE%). Услуга: %SERVICE_TITLE%. Мастер: %MASTER_NAME%. Время: %DATETIME%. Клиент ждет вашего звонка, свяжитесь с ним как можно скорее. Ваш сервис REONE'
            ],
            self::TYPE_NOTIFY_STAFF_REMOVE_RECORD_INET     => [
                'Уведомление мастеру об удалении записи через интернет *',
                'Удалена запись: %CLIENT_NAME% (%CLIENT_PHONE%); Услуга: %SERVICE_TITLE%; Сотрудник: %MASTER_NAME% на %DATETIME%'
            ],
            self::TYPE_REQUEST_CONFIRM_VIA_INET            => [
                'SMS-подтверждение записи через Интернет *',
                'Ваша запись в %COMPANY_NAME% подтверждена. Специалист: %MASTER_NAME%, Услуга: %SERVICE_TITLE%, Время: %DATETIME%, %COMPANY_ADDRESS%, %CONTACT_PHONE%'
            ],
            self::TYPE_NOTIFY_LOW_BALANCE                  => [
                'Уведомление о низком балансе *',
                'empty'
            ],
            self::TYPE_NOTIFY_STAFF_WORKTIME_EXPIRE        => [
                'Уведомление о скором завершении расписания работы сотрудников *',
                'empty'
            ],
            self::TYPE_VISIT_REMIND                        => [
                'Напоминание клиенту о предстоящем визите',
                'Не забудьте Ваш визит в %COMPANY_NAME% в %HOURMINUTES% (%DATE%), %COMPANY_ADDRESS%'
            ],
            self::TYPE_NOTIFY_CUSTOMER_SINCE_LAST_VISIT    => [
                'Отправлять клиенту сообщение с момента последнего визита после',
                ''
            ],
            self::TYPE_NOTIFY_HEALTH_EXAMINATION    => [
                "Напоминание клиенту о профосмотре",
                ""
            ]
        ];
    }

    /**
     * @param $name
     * @param $description
     * @param $template
     * @param null $company_id
     * @return CustomerRequestTemplate|static
     */
    public static function initSettings($name, $description, $template, $company_id = null)
    {
        if (!$company_id) {
            $company_id = Yii::$app->user->identity->company_id;
        }

        $key = CustomerRequestTemplate::findOne(['key' => $name, 'company_id' => $company_id]);

        if (!$key) {
            $key = new CustomerRequestTemplate();
            $key->key = strval($name);
            $key->template = $template;
            $key->company_id = $company_id;

            $defaultEnabledTemplates = self::getDefaultEnabledTemplate();
            if (in_array($key->key, $defaultEnabledTemplates)) {
                $key->is_enabled = true;
            } else {
                $key->is_enabled = false;
            }

            $key->save();
        }

        $key->description = $description;
        return $key;
    }

    /**
     * Return excluded templates
     * @return array
     */
    public static function getExcludedTemplates()
    {
        return [
            self::TYPE_NOTIFY_LOW_BALANCE,
            self::TYPE_NOTIFY_STAFF_WORKTIME_EXPIRE,
            self::TYPE_NOTIFY_STAFF_REMOVE_RECORD_INET,
            self::TYPE_NOTIFY_ADMIN_REMOVE_RECORD_INET,
            self::TYPE_REQUEST_CONFIRM_VIA_INET,

            self::TYPE_NOTIFY_CLIENT_ABOUT_RECORD,
            self::TYPE_REQUEST_WITH_RECORD_INFO,
            self::TYPE_NOTIFY_RECORD_REMOVAL,
            self::TYPE_NOTIFY_STAFF_NEW_RECORD_INET,
            self::TYPE_NOTIFY_CLIENT_DISCOUNT_EXPIRE,
            self::TYPE_NOTIFY_ADMIN_NEW_RECORD_INET,
            self::TYPE_REQUEST_COMMENT_AFTER_VISIT_ONLINE,
            self::TYPE_REQUEST_COMMENT_AFTER_VISIT_OFFLINE,
            self::TYPE_NOTIFY_CLIENT_NEW_DISCOUNT
        ];
    }

    /**
     * Returns default enabled templates
     * @return array
     */
    public static function getDefaultEnabledTemplate()
    {
        return [
            self::TYPE_VISIT_REMIND,
        ];
    }

    /**
     * Returns label of template
     * @param $key
     * @return null
     */
    public static function getTemplateName($key)
    {
        $templates = self::getTemplateDefaultSettings();
        if (isset($templates[$key])) {
            return $templates[$key][0];
        }
        // throw new \Exception("Template with such key does not exist")
        return null;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'         => 'id',
            "label"      => function () {
                return self::getTemplateDefaultSettings()[$this->key][0] ?? null;
            },
            'key',
            'template'   => 'template',
            'is_enabled',
            'is_delayed' => function () {
                return $this->isDelayedByDefault();
            },
            'quantity',
            'quantity_type',
        ];
    }

    /**
     * @return bool
     */
    public function isDelayedByDefault()
    {
        if (in_array($this->key, self::getDelayedTemplates())) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public static function getDelayedTemplates()
    {
        return [
            self::TYPE_NOTIFY_CUSTOMER_SINCE_LAST_VISIT
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }
}

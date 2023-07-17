<?php

namespace core\forms\order;

use core\forms\medCard\MedCardTabCommentManageForm;
use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\helpers\OrderHelper;
use core\models\customer\CustomerCategory;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\models\Staff;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $customer_surname
 * @property string $customer_patronymic
 * @property string $customer_birth_date
 * @property string $customer_medical_record_id
 * @property integer $customer_gender
 * @property string $note
 * @property string $color
 * @property string $datetime
 * @property integer $company_cash_id
 * @property integer $referrer_id
 * @property string $referrer_name
 * @property integer $hours_before
 * @property integer $staff_id
 * @property integer $division_id
 * @property integer $customer_source_id
 * @property string $customer_source_name
 * @property integer $company_customer_id
 * @property integer $insurance_company_id
 *
 * @property array $services
 * @property array $contacts
 * @property array $payments
 * @property array $comments
 * @property array $products
 * @property array $categories
 */
class OrderCreateForm extends Model
{
    const SCENARIO_API = 'api';

    public $company_customer_id;
    public $customer_name;
    public $customer_surname;
    public $customer_patronymic;
    public $customer_phone;
    public $customer_birth_date;
    public $customer_gender;
    public $customer_medical_record_id;
    public $company_cash_id;
    public $note;
    public $color;
    public $datetime;
    public $hours_before;
    public $staff_id;
    public $division_id;
    public $service_id;
    public $insurance_company_id;
    public $referrer_id;
    public $referrer_name = null;
    public $customer_source_id;
    public $customer_source_name = null;
    public $ignoreNameWarning;
    public $ignore_stock = false;

    public $contacts;
    public $services;
    public $products = [];
    public $payments = [];
    public $comments;
    public $child_tooth;
    public $tooth;

    public $categories;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_API] = [
            'customer_name',
            'customer_phone',
            'datetime',
            'staff_id',
            'division_id',
            'service_id'
        ];

        return $scenarios;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [
                ['company_cash_id', 'customer_name', 'datetime', 'hours_before', 'staff_id', 'services', 'division_id'],
                'required'
            ],
            [
                [
                    'customer_name',
                    'customer_surname',
                    'customer_patronymic',
                    'customer_phone',
                    'customer_medical_record_id',
                    'note',
                    'color',
                    'customer_source_name',
                    'referrer_name'
                ],
                'string'
            ],

            [['customer_surname', 'customer_patronymic', 'customer_medical_record_id'], 'default', 'value' => null],

            [
                'customer_name',
                'filter',
                'filter' => function ($customer_name) {
                    return ucwords(trim($customer_name));
                },
                'skipOnEmpty' => true,
            ],
            [
                'customer_surname',
                'filter',
                'filter' => function ($customer_surname) {
                    return ucwords(trim($customer_surname));
                },
                'skipOnEmpty' => true,
            ],
            [
                'customer_patronymic',
                'filter',
                'filter' => function ($customer_patronymic) {
                    return ucwords(trim($customer_patronymic));
                },
                'skipOnEmpty' => true,
            ],
            ['customer_phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],

            ['customer_birth_date', 'date', 'format' => 'php:Y-m-d'],

            ['customer_gender', 'integer'],
            ['customer_gender', 'in', 'range' => array_keys(GenderHelper::getGenders())],

            [['ignore_stock', 'ignoreNameWarning'], 'boolean'],

            ['color', 'in', 'range' => array_keys(OrderHelper::getCssClasses())],

            ['datetime', 'date', 'format' => 'php:Y-m-d H:i'],

            [
                [
                    'company_cash_id',
                    'customer_source_id',
                    'staff_id',
                    'insurance_company_id',
                    'referrer_id',
                    'division_id',
                    'company_customer_id'
                ],
                'integer'
            ],
            [['hours_before'], 'integer', 'min' => 0],

            [
                ['division_id'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],
            [
                ['company_cash_id'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => CompanyCash::className(),
                'targetAttribute' => ['company_cash_id' => 'id']
            ],

            ['staff_id', 'validateStaff'],

            ['payments', 'validatePayments'],
            ['contacts', 'validateContacts'],
            ['services', 'validateServices'],
            ['products', 'validateProducts'],
            ['comments', 'validateComments'],
            [['child_tooth', 'tooth'], 'validateTooth'],

            ['categories', 'each', 'rule' => ['integer']],
            [
                'categories',
                'each',
                'rule' => ['exist', 'targetClass' => CustomerCategory::class, 'targetAttribute' => 'id']
            ],

            ['service_id', 'required', 'on' => self::SCENARIO_API],
            ['service_id', 'integer', 'on' => self::SCENARIO_API],
            [
                'service_id',
                'exist',
                'targetClass' => DivisionService::class,
                'targetAttribute' => 'id',
                'on' => self::SCENARIO_API
            ],
        ];
    }

    /**
     * ToDo move this to controller, add rule to create order permission
     * @param $attribute
     * @param $params
     */
    public function validateStaff($attribute, $params)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $staff = Yii::$app->user->identity->staff;

        if ($staff && !$staff->create_order) {
            $this->addError($attribute, Yii::t('app', 'Not allowed to create order'));
        }
    }

    public function validateProducts($attribute, $params)
    {
        foreach ($this->products as $product) {
            if (empty($product['product_id'])) {
                continue;
            }
            $productForm = new OrderProductCreateForm();
            $productForm->product_id = $product['product_id'];
            $productForm->quantity = $product['quantity'];
            $productForm->price = $product['price'];
            if (!$productForm->validate()) {
                $error_message = Yii::t('app', 'Product error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($productForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validateServices($attribute, $params)
    {
        foreach ($this->services as $service) {
            $serviceForm = new OrderServiceManageForm();
            $serviceForm->duration = $service['duration'];
            $serviceForm->division_service_id = $service['division_service_id'];
            $serviceForm->discount = $service['discount'];
            $serviceForm->price = $service['price'];
            $serviceForm->quantity = $service['quantity'];
            if (!$serviceForm->validate()) {
                $error_message = Yii::t('app', 'Service error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($serviceForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validateContacts($attribute, $params)
    {
        foreach ($this->contacts as $contact) {
            $contactForm = new OrderContactForm();
            $contactForm->id = $contact['id'];
            $contactForm->name = $contact['name'];
            $contactForm->phone = $contact['phone'];
            if (!$contactForm->validate()) {
                $error_message = Yii::t('app', 'Contact error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($contactForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validateComments($attribute, $params)
    {
        foreach ($this->comments as $comment_template_category_id => $comment) {
            $commentForm = new MedCardTabCommentManageForm();
            $commentForm->comment = $comment;
            $commentForm->comment_template_category_id = $comment_template_category_id;
            if (!$commentForm->validate()) {
                $error_message = Yii::t('app', 'Comment error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($commentForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validatePayments($attribute, $params)
    {
        foreach ($this->payments as $payment) {
            if (empty($payment['amount'])) {
                continue;
            }
            $paymentForm = new OrderPaymentCreateForm();
            $paymentForm->payment_id = $payment['payment_id'];
            $paymentForm->amount = $payment['amount'];
            $paymentForm->setInsuranceCompanyId($this->insurance_company_id);
            if (!$paymentForm->validate()) {
                $error_message = Yii::t('app', 'Payment error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($paymentForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validateTooth($attribute, $params)
    {
        $tooth = Json::decode($this->tooth);
        if (!is_array($tooth)) {
            return;
        }

        foreach ($tooth as $teeth_num) {
            if (!is_integer($teeth_num)) {
                $this->addError($attribute, Yii::t('app', 'Teeth error'));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'start_date' => Yii::t('app', 'From'),
            'end_date'   => Yii::t('app', 'To'),
            'services'   => Yii::t('app', 'Services'),
            'payments'   => Yii::t('app', 'Payments'),
            'tooth'      => Yii::t('app', 'Tooth'),
            'categories' => Yii::t('app', 'Categories'),
        ];
    }

    public function formName()
    {
        return 'Order';
    }
}

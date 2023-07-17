<?php

namespace core\forms\order;

use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\helpers\OrderHelper;
use core\models\customer\CustomerCategory;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;
use core\repositories\StaffRepository;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @property string $customer_name
 * @property string $customer_surname
 * @property string $customer_patronymic
 * @property string $customer_phone
 * @property string $customer_birth_date
 * @property integer $customer_gender
 * @property string $note
 * @property string $color
 * @property integer $company_cash_id
 * @property integer $insurance_company_id
 * @property integer $referrer_id
 * @property string $referrer_name
 * @property integer $hours_before
 * @property integer $customer_source_id
 * @property string $customer_source_name
 * @property string $datetime
 * @property integer $staff_id
 * @property integer $division_id
 * @property integer $company_customer_id
 * @property boolean $services_disabled
 *
 * @property array $services
 * @property array $payments
 * @property array $comments
 * @property array $contacts
 * @property array $products
 * @property array $categories
 *
 * @property Order $order
 */
class OrderUpdateForm extends Model
{
    public $company_customer_id;
    public $customer_name;
    public $customer_surname;
    public $customer_patronymic;
    public $customer_phone;
    public $customer_birth_date;
    public $customer_gender;
    public $company_cash_id;
    public $note;
    public $color;
    public $hours_before;
    public $datetime;
    public $staff_id;
    public $division_id;
    public $insurance_company_id;
    public $referrer_id;
    public $referrer_name = null;
    public $customer_source_id;
    public $customer_source_name = null;
    public $ignoreNameWarning;
    public $ignore_stock = false;
    public $services_disabled = false;

    public $services;
    public $products;
    public $payments;
    public $contacts;

    public $categories;

    public $order;
    private $staffRepository;

    public function __construct(Order $order, $config = [])
    {
        $this->order = $order;
        $customer = $order->companyCustomer->customer;
        $this->customer_name = $customer->name;
        $this->customer_surname = $customer->lastname;
        $this->customer_patronymic = $customer->patronymic;
        $this->customer_phone = $customer->phoneIsMasked() ? null : $customer->phone;
        $this->customer_birth_date = $customer->birth_date;
        $this->customer_gender = $customer->gender;
        $this->customer_source_id = $order->companyCustomer->source_id;
        $this->company_customer_id = $order->company_customer_id;
        $this->company_cash_id = $order->company_cash_id;
        $this->insurance_company_id = $order->insurance_company_id;
        $this->referrer_id = $order->referrer_id;
        $this->note = $order->note;
        $this->color = $order->color;
        $this->hours_before = $order->hours_before;
        $this->datetime = (new \DateTime($order->datetime))->format("Y-m-d H:i");
        $this->staff_id = $order->staff_id;
        $this->division_id = $order->division_id;
        $this->services_disabled = $order->services_disabled;

        $this->services = OrderService::map($order->orderServices);
        $this->payments = $order->orderPayments;
        $this->categories = $order->companyCustomer->getCategories()->select('id')->column();

        $this->staffRepository = new StaffRepository();

        parent::__construct($config);
    }


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [
                [
                    'customer_name',
                    'hours_before',
                    'services',
                    'company_cash_id',
                    'datetime',
                    'staff_id',
                    'division_id',
//                    'company_customer_id'
                ],
                'required'
            ],
            [
                [
                    'customer_name',
                    'customer_surname',
                    'customer_patronymic',
                    'customer_phone',
                    'note',
                    'color',
                    'customer_source_name',
                    'referrer_name'
                ],
                'string'
            ],

            [
                'customer_name',
                'filter',
                'filter' => function ($customer_name) {
                    return ucwords(trim($customer_name));
                }
            ],
            [
                'customer_surname',
                'filter',
                'filter' => function ($customer_surname) {
                    return ucwords(trim($customer_surname));
                }
            ],
            [
                'customer_patronymic',
                'filter',
                'filter' => function ($customer_patronymic) {
                    return ucwords(trim($customer_patronymic));
                }
            ],
            ['customer_phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],

            ['customer_birth_date', 'date', 'format' => 'php:Y-m-d'],

            ['customer_gender', 'integer'],
            ['customer_gender', 'in', 'range' => array_keys(GenderHelper::getGenders())],

            [['ignore_stock', 'ignoreNameWarning', 'services_disabled'], 'boolean'],

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
                'company_cash_id',
                'exist',
                'skipOnError'     => false,
                'targetClass'     => CompanyCash::className(),
                'targetAttribute' => ['company_cash_id' => 'id']
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],

            ['payments', 'validatePayments'],
            ['contacts', 'validateContacts'],
            ['services', 'validateServices'],
            ['products', 'validateProducts'],
            ['staff_id', 'validateStaff'],

            ['categories', 'each', 'rule' => ['integer']],
            [
                'categories',
                'each',
                'rule' => ['exist', 'targetClass' => CustomerCategory::class, 'targetAttribute' => 'id']
            ],
        ];
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
                $this->addError($attribute, Yii::t('app', 'Product error'));
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
            $serviceForm->order_service_id = $service['order_service_id'] ?? null;
            if (!$serviceForm->validate()) {
                $this->addError($attribute, Yii::t('app', 'Service error'));
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
                $this->addError($attribute, Yii::t('app', 'Payment error'));
            }
        }
    }

    public function validateStaff($attribute, $params)
    {
        $staff = $this->staffRepository->find($this->staff_id);
        $staffServiceIds = ArrayHelper::getColumn($staff->divisionServices, 'id');
        foreach ($this->services as $service) {
            if (!in_array($service['division_service_id'], $staffServiceIds)) {
                $this->addError($attribute, Yii::t('app', 'Staff does not provide this service'));
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

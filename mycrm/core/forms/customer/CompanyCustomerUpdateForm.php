<?php

namespace core\forms\customer;

use common\components\Model;
use core\forms\PhoneForm;
use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\models\customer\CompanyCustomer;
use Yii;

/**
 * @property string $name
 * @property string $lastname
 * @property string $patronymic
 * @property string $phone
 * @property string $email
 * @property integer $gender
 * @property string $birth_date
 * @property string $address
 * @property string $city
 * @property array $categories
 * @property array $phones
 * @property integer $source_id
 * @property integer $insurance_company_id
 * @property string $comments
 * @property boolean $sms_birthday
 * @property boolean $sms_exclude
 * @property integer $balance
 * @property string $iin
 * @property string $id_card_number
 * @property string $job
 * @property string $employee
 * @property integer $discount
 * @property string $medical_record_id
 *
 * @property CompanyCustomer $companyCustomer
 */
class CompanyCustomerUpdateForm extends Model
{
    public $name;
    public $lastname;
    public $phone;
    public $email;
    public $gender;
    public $birth_date;
    public $address;
    public $city;
    public $categories;
    public $source_id;
    public $comments;
    public $sms_birthday;
    public $sms_exclude;
    public $balance;
    public $imageFile;
    public $discount;
    public $patronymic;
    public $phones;
    public $insurance_company_id;
    public $medical_record_id;

    public $employer;
    public $id_card_number;
    public $iin;
    public $job;

    public $cashback_percent;

    public $insurance_policy_number;
    public $insurer;
    public $insurance_expire_date;

    public $companyCustomer;

    public function __construct(CompanyCustomer $companyCustomer, $config = [])
    {
        $this->companyCustomer = $companyCustomer;
        $customer              = $companyCustomer->customer;

        $this->name           = $customer->name;
        $this->lastname       = $customer->lastname;
        $this->patronymic     = $customer->patronymic;
        $this->phone          = $customer->phone;
        $this->email          = $customer->email;
        $this->gender         = $customer->gender;
        $this->birth_date     = $customer->birth_date;
        $this->id_card_number = $customer->id_card_number;
        $this->iin            = $customer->iin;

        $this->insurance_company_id = $companyCustomer->insurance_company_id;

        $this->discount     = $companyCustomer->discount;
        $this->address = $companyCustomer->address;
        $this->balance = $companyCustomer->balance;
        $this->city = $companyCustomer->city;
        $this->source_id = $companyCustomer->source_id;
        $this->comments = $companyCustomer->comments;
        $this->sms_birthday = $companyCustomer->sms_birthday;
        $this->sms_exclude = $companyCustomer->sms_exclude;
        $this->job = $companyCustomer->job;
        $this->employer = $companyCustomer->employer;
        $this->cashback_percent = $companyCustomer->cashback_percent;
        $this->insurance_policy_number = $companyCustomer->insurance_policy_number;
        $this->insurer = $companyCustomer->insurer;
        $this->insurance_expire_date = $companyCustomer->insurance_expire_date;
        $this->medical_record_id = $companyCustomer->medical_record_id;

        $this->categories = $companyCustomer->getCategories()
            ->select('id')
            ->column();
        $this->phones = array_map(function ($phone) {
            return ['value' => $phone];
        }, $companyCustomer->getPhones()
            ->select('phone')
            ->column());

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],

            ['email', 'email'],
            [['gender', 'source_id', 'balance', 'insurance_company_id'], 'integer'],
            [['name', 'lastname', 'patronymic', 'city', 'phone', 'job', 'employer'], 'string'],
            [['sms_birthday', 'sms_exclude'], 'boolean'],
            [['comments', 'address'], 'safe'],

            // ['name', 'match', 'pattern' => '/^[a-zа-яё]+$/iu'],
            // ['lastname', 'match', 'pattern' => '/^[a-zа-яё]+(-[a-zа-яё]+)?$/iu'],
            ['phone', 'default', 'value' => null],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d'],
            ['gender', 'in', 'range' => array_keys(GenderHelper::getGenders())],

            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],

            [['id_card_number', 'iin', 'insurance_company_id'], 'default', 'value' => null],
            ['iin', 'match', 'pattern' => '/^[0-9]{12}$/'],
            ['iin', 'string', 'min' => 12, 'max' => 12],
            ['id_card_number', 'match', 'pattern' => '/^[0-9]{9}$/'],
            ['id_card_number', 'string', 'min' => 9, 'max' => 9],

            [['iin', 'id_card_number'], 'uniqueCompanyCustomer'],

            [['discount', 'cashback_percent'], 'default', 'value' => 0],
            [['discount', 'cashback_percent'], 'integer', 'min' => 0, 'max' => 100],

            ['categories', 'default', 'value' => []],
            ['categories', 'each', 'rule' => ['integer']],

            ['insurer', 'string', 'max' => 255],
            ['insurance_policy_number', 'string', 'max' => 255],
            ['insurance_expire_date', 'date', 'format' => 'yyyy-MM-dd'],

            ['medical_record_id', 'string'],

            ['phones', 'default', 'value' => []],
            ['phones', 'validatePhones'],
            [
                'phones',
                'filter',
                'filter'      => function ($data) {
                    return array_unique(array_filter(array_map(function ($phoneData) {
                        return $phoneData['value'] ?? null;
                    }, $data)));
                },
                'skipOnEmpty' => true
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'                    => Yii::t('app', 'Customer Name'),
            'lastname'                => Yii::t('app', 'Last Name'),
            'phone'                   => Yii::t('app', 'Phone'),
            'email'                   => Yii::t('app', 'Email'),
            'gender'                  => Yii::t('app', 'Gender'),
            'birth_date'              => Yii::t('app', 'Birth Date'),
            'sms_birthday'            => Yii::t('app', 'SMS birthday'),
            'sms_exclude'             => Yii::t('app', 'SMS exclude'),
            'categories'              => Yii::t('app', 'Categories'),
            'comments'                => Yii::t('app', 'Description'),
            'address'                 => Yii::t('app', 'Address'),
            'city'                    => Yii::t('app', 'City'),
            'source_id'               => Yii::t('app', 'Customer Source'),
            'balance'                 => Yii::t('app', 'Balance'),
            'job'                     => Yii::t('app', 'Job'),
            'employer'                => Yii::t('app', 'Employer'),
            'iin'                     => Yii::t('app', 'IIN'),
            'id_card_number'          => Yii::t('app', 'ID Card number'),
            'discount'                => Yii::t('app', 'Discount'),
            'patronymic'              => Yii::t('app', 'Patronymic'),
            'cashback_percent'        => Yii::t('app', 'Cashback Percent'),
            'insurance_company_id'    => Yii::t('app', 'Insurance Company'),
            'insurer'                 => Yii::t('app', 'Insurer'),
            'insurance_policy_number' => Yii::t('app', 'Insurance policy number'),
            'insurance_expire_date'   => Yii::t('app', 'Insurance is valid until'),
            'medical_record_id'       => Yii::t('app', 'Number of medical record'),
            'phones'                  => Yii::t('app', 'Phones')
        ];
    }

    /**
     * Validates the company customer uniqueness.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function uniqueCompanyCustomer($attribute, $params)
    {
        $companyCustomerExists = CompanyCustomer::find()
            ->joinWith('customer')
            ->where(["{{%customers}}.{$attribute}" => $this->{$attribute}, 'company_id' => Yii::$app->user->getIdentity()->company_id])
            ->andWhere(['<>', '{{%customers}}.id', $this->companyCustomer->customer_id])
            ->exists();
        if ($companyCustomerExists) {
            $this->addError($attribute, Yii::t('app', 'Customer with such "{attribute}" is already exist', [
                'attribute' => $this->getAttributeLabel($attribute)
            ]));
        }
    }


    /**
     * @param $attribute
     */
    public function validatePhones($attribute)
    {
        foreach ($this->{$attribute} as $key => $phoneData) {
            $form = new PhoneForm(['phone' => $phoneData['value'] ?? null]);

            if (!$form->validate()) {
                foreach ($form->firstErrors as $name => $error) {
                    $this->addError("{$attribute}[{$key}][{$name}]", $error);
                }
            }
        }

    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}

<?php

namespace core\models\customer;

use common\components\HistoryBehavior;
use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\models\division\DivisionReview;
use core\models\Image;
use core\models\order\Order;
use core\models\StaffReview;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%customers}}".
 *
 * @property integer             $id
 * @property string              $phone
 * @property string              $access_token
 * @property string              $name
 * @property string              $lastname
 * @property string              $patronymic
 * @property string              $email
 * @property boolean             $gender
 * @property string              $birth_date
 * @property integer             $image_id
 * @property string              $created_time
 * @property string              $key_ios
 * @property string              $key_android
 * @property string              $password_hash
 * @property string              $salt
 * @property string              $forgot_hash
 * @property string              $iin
 * @property string              $id_card_number
 *
 * @property CompanyCustomer[]   $companyCustomers
 * @property CustomerFavourite[] $customerFavourites
 * @property CustomerRequest[]   $customerRequests
 * @property Image               $image
 * @property DivisionReview[]    $divisionReviews
 * @property StaffReview[]       $staffReviews
 * @property CompanyCustomer     $companyCustomer
 * @property CustomerHistory[]   $histories
 */
class Customer extends \yii\db\ActiveRecord implements IdentityInterface
{
    const TOKEN_SIZE = 32;

    /**
     * Push key types
     */
    const KEY_IOS = 1;
    const KEY_ANDROID = 2;

    /**
     * @param string $phone
     * @param string $name
     * @param string $lastName
     * @param integer $gender
     * @param string $birth_date
     * @param string $email
     * @param string $iin
     * @param string $id_card_number
     * @param string|null $patronymic
     * @param integer $image_id
     * @param string $password_hash
     * @param string $key_ios
     * @param string $key_android
     * @return Customer
     * @throws \yii\base\Exception
     */
    public static function add(
        $phone,
        $name,
        $lastName,
        $gender,
        $birth_date = null,
        $email = null,
        $iin = null,
        $id_card_number = null,
        $patronymic = null,
        $image_id = null,
        $password_hash = null,
        $key_ios = null,
        $key_android = null
    )
    {
        $model = new Customer();
        $model->created_time = date('Y-m-d H:i:s');
        $model->forgot_hash = null;
        $model->phone = empty($phone) ? null : $phone;
        $model->name = $name;
        $model->lastname = $lastName;
        $model->email = $email;
        $model->birth_date = $birth_date;
        $model->gender = $gender ?: GenderHelper::GENDER_UNDEFINED;
        $model->image_id = $image_id;
        $model->key_ios = $key_ios;
        $model->key_android = $key_android;
        $model->iin = $iin;
        $model->id_card_number = $id_card_number;
        $model->patronymic = $patronymic;
        $model->access_token = Yii::$app->security->generateRandomString(self::TOKEN_SIZE);

//        $model->salt = Yii::$app->security->generateRandomString();
//        $model->password_hash = Yii::$app->security->generatePasswordHash($password_hash . $model->salt);

        return $model;
    }

    /**
     * @param string $name
     * @param string $last_name
     * @param string $patronymic
     */
    public function rename($name, $last_name, $patronymic)
    {
        if ( ! empty($name)) {
            $this->name = $name;
        }
        if ($last_name !== null) {
            $this->lastname = $last_name;
        }
        if ($patronymic !== null) {
            $this->patronymic = $patronymic;
        }
        $this->guardNameIsEmpty();
    }

    /**
     * @param string $phone
     * @param string $email
     * @param string $gender
     * @param string $birth_date
     * @param string $iin
     * @param string $id_card_number
     */
    public function edit(
        $phone,
        $email,
        $gender,
        $birth_date,
        $iin,
        $id_card_number
    )
    {
        $this->phone = $phone;
        $this->email = $email;
        $this->gender = $gender ?: GenderHelper::GENDER_UNDEFINED;
        $this->birth_date = $birth_date;
        $this->iin = $iin;
        $this->id_card_number = $id_card_number;
    }

    /**
     * Method is overwritten to hide customer phone from any access
     * if user is not allowed to see it
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        try{
            if ($name == 'phone' && ! Yii::$app->user->isGuest && ! Yii::$app->user->identity->canSeeCustomerPhones()) {
                return self::maskPhone(parent::__get($name));
            }
        }catch (\Exception $e){
            return parent::__get($name);
        }

        return parent::__get($name);
    }

    /**
     * Protect phone from invalid format
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($name == 'phone') {
            if (preg_match(CustomerHelper::PHONE_VALIDATE_PATTERN, $value)){
                parent::__set($name, $value);
            }
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'name' => 'name',
            'surname' => 'lastname',
            'phone' => 'phone',
            'email' => 'email',
            'gender' => 'gender',
            'gender_title' => function () {
                return $this->getGenderName();
            },
            'birth_date' => 'birth_date',
            'id_card_number' => 'id_card_number',
            'iin' => 'iin',
            'order_count' => function () {
                return Order::find()
                    ->joinWith("companyCustomer")
                    ->where(['crm_company_customers.customer_id' => $this->id])
                    ->count();
            },
            'feedbacks_made' => function () {
                $staff_reviews_count = StaffReview::find()->where(["customer_id" => $this->id])->count();
                $division_reviews_count = DivisionReview::find()->where(["customer_id" => $this->id])->count();

                return $staff_reviews_count + $division_reviews_count;
            },
            'average_mark' => function () {
                $staff_reviews_count = StaffReview::find()->where(["customer_id" => $this->id])->count();
                $division_reviews_count = DivisionReview::find()->where(["customer_id" => $this->id])->count();

                $staff_reviews_sum = StaffReview::find()->where(["customer_id" => $this->id])->sum('value');
                $division_reviews_sum = DivisionReview::find()->where(["customer_id" => $this->id])->sum('value');

                $average_mark = 0;
                $total_reviews_count = $staff_reviews_count + $division_reviews_count;
                if ($total_reviews_count !== 0) {
                    $average_mark = ($staff_reviews_sum + $division_reviews_sum) / $total_reviews_count;
                }
                return $average_mark;
            },
            'avatar' => 'image',
            /* @deprecated */
            'image' => function () {
                return  $this->getAvatarImageUrl();
            },
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['gender', 'image_id'], 'integer'],
            [['phone', 'access_token', 'password_hash', 'forgot_hash', 'birth_date', 'name', 'lastname', 'key_ios', 'key_android'], 'string', 'max' => 255],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            [['email'], 'email'],
            [['birth_date'], 'date', 'format' => 'yyyy-mm-dd', 'skipOnEmpty' => true],
            [['iin', 'id_card_number'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => false, 'targetClass' => Image::className(), 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Customer Name'),
            'phone' => Yii::t('app', 'Phone'),
            'access_token' => Yii::t('app', 'Access Token'),
            'email' => Yii::t('app', 'Email'),
            'gender' => Yii::t('app','Gender'),
            'birth_date' => Yii::t('app','Birth Date'),
            'image_id' => Yii::t('app', 'Image ID'),
            'created_time' => Yii::t('app', 'Created Time'),
            'lastname' => Yii::t('app', 'Last Name'),
            'iin' => Yii::t('app', 'Iin'),
            'id_card_number' => Yii::t('app', 'Card Number'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomers()
    {
        return $this->hasMany(CompanyCustomer::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerFavourites()
    {
        return $this->hasMany(CustomerFavourite::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequests()
    {
        return $this->hasMany(CustomerRequest::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionReviews()
    {
        return $this->hasMany(DivisionReview::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffReviews()
    {
        return $this->hasMany(StaffReview::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->access_token;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $access_token the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($access_token)
    {
        return $this->access_token === $access_token;
    }

    /**
     * Password validation.
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password . $this->salt, $this->password_hash);
    }

    /**
     * Generates secure key.
     */
    public function regenerateAccessToken()
    {
        $this->generateToken();
        if ($this->save())
            return $this->access_token;
        else
            return null;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->generateToken();
            }
            return true;
        }

        return false;
    }

    /**
     * Generates secure key.
     */
    private function generateToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString(self::TOKEN_SIZE);
    }

    /**
     * Returns customer by phone number or creates
     * @param string $phone
     * @param string | null $name
     * @param string | null $email
     * @return Customer | null
     */
    public static function getCustomer($phone, $name = null, $email = null)
    {
        $model = Customer::find()->where(["phone" => $phone])->one();
        if(!$model)
        {
            $model = new Customer();
            $model->phone = $phone;
        }
        $model->name = $name;
        $model->email = $email;

        return $model->save() ? $model : null;
    }

    /**
     * @deprecated
     * returns CompanyCustomer for current Customer. It also gets current Company_id and equates it for CompanyCustomer.
     * If no CompanyCustomer is found, the new one will be created.
     *
     * @return CompanyCustomer
     */
    public function getCompanyCustomer() {
        $company_id = \Yii::$app->user->identity->company_id;
        $companyCustomer = CompanyCustomer::find()->customer($this->id)->company($company_id)->one();

        if(!$companyCustomer) {
            $model = new CompanyCustomer();
            $model->customer_id = $this->id;

            $model->rank = CompanyCustomer::RANK_NONE;
            $model->discount = 0;

            $model->sms_birthday = true;
            $model->sms_exclude = false;

            $model->company_id = $company_id;

            return $model->save() ? $model : 'error creating CompanyCustomer';
        }

        return $companyCustomer;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(CustomerHistory::className(), ['row_id' => 'id']);
    }

    /**
     * Returns customer image path
     *
     * @return string
     */
    public function getAvatarImageUrl()
    {
        return $this->image_id ? $this->image->getAvatarImageUrl() : \Yii::$app->params['crm_host'] . "/image/def_client_img.jpg";
    }

    /**
     * Returns customer age
     *
     * @return integer
     */
    public function getAge()
    {
        if (isset($this->birth_date)) {
            $birth_date = new \DateTime($this->birth_date);
            $difference = $birth_date->diff(new \DateTime(), true);

            return $difference->format("%y");
        } else {
            return Yii::t('app', 'Unknown');
        }
    }

    /**
     * Returns gender
     * @return string
     */
    public function getGenderName()
    {
        $genders_list = GenderHelper::getGenders();
        return isset($genders_list[$this->gender]) ? $genders_list[$this->gender] : $genders_list[GenderHelper::GENDER_UNDEFINED];
    }

    /**
     * Returns customer information
     * @return array
     */
    public function getInformation()
    {
        $order_count = Order::find()
            ->joinWith("companyCustomer")
            ->where(['crm_company_customers.customer_id' => $this->id])
            ->count();
        $staff_reviews_count = StaffReview::find()->where(["customer_id" => $this->id])->count();
        $division_reviews_count = DivisionReview::find()->where(["customer_id" => $this->id])->count();

        $staff_reviews_sum = StaffReview::find()->where(["customer_id" => $this->id])->sum('value');
        $division_reviews_sum = DivisionReview::find()->where(["customer_id" => $this->id])->sum('value');

        $average_mark = 0;
        $total_reviews_count = $staff_reviews_count + $division_reviews_count;
        if ($total_reviews_count !== 0)
        {
            $average_mark = ($staff_reviews_sum + $division_reviews_sum) / $total_reviews_count;
        }

        return [
            'name' => $this->name,
            'surname' => $this->lastname,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'gender_title' => $this->getGenderName(),
            'birth_date' => $this->birth_date,
            'image' => $this->getAvatarImageUrl(),
            'order_count' => $order_count,
            'feedbacks_made' => $total_reviews_count,
            'average_mark' => $average_mark,
        ];
    }

    /**
     * Returns full name
     * @return string
     */
    public function getFullName()
    {
        $full_name = $this->name;
        if (!empty($this->lastname)) {
            $full_name = $this->lastname . ' ' . $full_name;
        }
        if (!empty($this->patronymic)) {
            $full_name .= " " . $this->patronymic;
        }
        return $full_name;
    }

    /**
     * Returns full name
     * @return string
     */
    public function getFullInfo()
    {
        if (!empty($this->phone)) {
            return $this->getFullName() . " ({$this->phone})";
        }
        return $this->getFullName();
    }

    /**
     * Update push key
     * @param integer $type
     * @param string $key
     * @return boolean
     */
    public function setKey($type, $key)
    {
        if ($type == self::KEY_IOS)
        {
            $this->key_ios = $key;
        }
        else if ($type == self::KEY_ANDROID)
        {
            $this->key_android = $key;
        }

        return $this->save();
    }

    /**
     * Disable deletion
     * @inheritdoc
     */
    public function beforeDelete()
    {
        return false;
    }

    /**
     * Find model by phone.
     * @param string $phone Customer
     * @return \yii\db\ActiveRecord Customer
     */
    public static function findByPhone($phone)
    {
        return Customer::find()->where(['phone' => $phone])->one();
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

    private function guardNameIsEmpty()
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Name is empty');
        }
    }

    public static function maskPhone($phone)
    {
        return preg_match(CustomerHelper::PHONE_VALIDATE_PATTERN, $phone) ?
               substr_replace($phone,'* **',9,4) : '';
    }

    public function phoneIsMasked()
    {
        return strpos($this->phone, '* **') !== false;
    }
}

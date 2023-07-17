<?php

namespace core\models\customer;

use core\helpers\customer\CustomerLoyaltyHelper;
use core\models\company\Company;
use core\models\customer\query\CustomerLoyaltyQuery;
use Yii;

/**
 * This is the model class for table "crm_customer_loyalties".
 *
 * @property integer $id
 * @property integer $mode
 * @property integer $event
 * @property integer $amount
 * @property integer $discount
 * @property integer $category_id
 * @property integer $company_id
 *
 * @property CustomerCategory $category
 * @property Company $company
 */
class CustomerLoyalty extends \yii\db\ActiveRecord
{
    const EVENT_MONEY = 0;
    const EVENT_VISIT = 1;
    const EVENT_DAY = 2;

    const MODE_ADD_DISCOUNT = 0;
    const MODE_REMOVE_DISCOUNT = 1;
    const MODE_ADD_CATEGORY = 4;
    const MODE_REMOVE_CATEGORY = 5;

    const SCENARIO_DISCOUNT = "discount";
    const SCENARIO_CATEGORY = "category";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_loyalties}}';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT  => ['mode', 'company_id'],
            self::SCENARIO_DISCOUNT => ['mode', 'amount', 'event', 'company_id', 'discount'],
            self::SCENARIO_CATEGORY => ['mode', 'amount', 'event', 'company_id', 'category_id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mode', 'event', 'amount', 'discount', 'category_id', 'company_id'], 'integer'],
            [['mode', 'event', 'amount', 'discount', 'category_id', 'company_id'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'mode'        => Yii::t('app', 'Customer Loyalty'),
            'event'       => Yii::t('app', 'Condition'),
            'amount'      => Yii::t('app', 'Amount'),
            'discount'    => Yii::t('app', 'Discount'),
            'category'    => Yii::t('app', 'Category'),
            'category_id' => Yii::t('app', 'Category'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(CustomerCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'category_id']);
    }

    public function defineScenario()
    {
        switch ($this->mode) {
            case CustomerLoyalty::MODE_REMOVE_DISCOUNT:
                $this->discount = 0;
            case CustomerLoyalty::MODE_ADD_DISCOUNT:
                $this->scenario = CustomerLoyalty::SCENARIO_DISCOUNT;
                break;

            case CustomerLoyalty::MODE_ADD_CATEGORY:
            case CustomerLoyalty::MODE_REMOVE_CATEGORY:
                $this->scenario = CustomerLoyalty::SCENARIO_CATEGORY;
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Fetches the array of CustomerLoyalty based on Company that the
     * current User is related to.
     * @return CustomerLoyalty[] the loaded models
     */
    public static function getPositiveLoyalties()
    {
        return self::find()
            ->where([
                'or',
                ['mode' => self::MODE_ADD_DISCOUNT],
                ['mode' => self::MODE_ADD_CATEGORY],
            ])
            ->company()
            ->joinWith('category')
            ->all();
    }

    public static function getNegativeLoyalties()
    {
        return self::find()
            ->where([
                'or',
                ['mode' => self::MODE_REMOVE_DISCOUNT],
                ['mode' => self::MODE_REMOVE_CATEGORY],
            ])
            ->company()
            ->joinWith('category')
            ->all();
    }

    public function fields()
    {
        return [
            'id',
            'customer_loyalty_id'  => 'id',
            'mode',
            'event',
            'amount',
            'discount',
            'rank',
            'category_id',
            'customer_category_id' => 'category_id',
            'company_id',
            'trigger_title'        => function () {
                return $this->getTriggerTitle();
            },
            'event_title'          => function () {
                return $this->getEventTitle();
            },
        ];
    }

    /**
     * Returns event trigger title
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function getTriggerTitle(): string
    {
        if ($this->event === self::EVENT_VISIT) {
            return Yii::t('app', 'Trigger VISIT {amount}',
                ['amount' => Yii::$app->formatter->asDecimal($this->amount)]
            );
        } elseif ($this->event === self::EVENT_MONEY) {
            return Yii::t('app', 'Trigger MONEY {amount}',
                ['amount' => Yii::$app->formatter->asDecimal($this->amount)]
            );
        } elseif ($this->event === self::EVENT_DAY) {
            return Yii::t('app', 'Trigger DAY {amount}',
                ['amount' => Yii::$app->formatter->asDecimal($this->amount)]
            );
        }
        return '';
    }

    public function getEventTitle(): string
    {
        if ($this->isDiscountMode()) {
            return Yii::t('app', 'Event SET DISCOUNT {amount}',
                ['amount' => $this->discount]);
        } elseif ($this->mode === self::MODE_REMOVE_CATEGORY) {
            return Yii::t('app', 'Event REMOVE CATEGORY {category}',
                ['category' => $this->category->name]);
        } elseif ($this->mode === self::MODE_ADD_CATEGORY) {
            return Yii::t('app', 'Event ADD CATEGORY {category}',
                ['category' => $this->category->name]);
        }
        return '';
    }

    /**
     * Return whether mode is related to category
     *
     * @return bool
     */
    public function isCategoryMode(): bool
    {
        return in_array($this->mode, [
            CustomerLoyalty::MODE_ADD_CATEGORY,
            CustomerLoyalty::MODE_REMOVE_CATEGORY,
        ]);
    }

    /**
     * Return whether mode is related to category
     *
     * @return bool
     */
    public function isDiscountMode(): bool
    {
        return in_array($this->mode, [
            CustomerLoyalty::MODE_ADD_DISCOUNT,
            CustomerLoyalty::MODE_REMOVE_DISCOUNT,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CustomerLoyaltyQuery(get_called_class());
    }

    /**
     * @param CompanyCustomer $customer
     * @return int
     */
    public function getAmount(CompanyCustomer $customer)
    {
        switch ($this->event) {
            case CustomerLoyalty::EVENT_MONEY:
                return $customer->revenue;
                break;
            case CustomerLoyalty::EVENT_VISIT:
                return $customer->getFinishedOrdersCount();
                break;
            case CustomerLoyalty::EVENT_DAY:
                $currentDay = new \DateTime();
                $lastVisit = $customer->getLastVisitDateTime();
                return $lastVisit
                    ? $currentDay->diff($lastVisit)->format("%a")
                    : -1;
            default:
                break;
        }
    }

    /**
     * @param CompanyCustomer $customer
     */
    public function process(CompanyCustomer $customer)
    {
        if ($this->getAmount($customer) >= $this->amount) {
            $program = CustomerLoyaltyHelper::getProgramInstance($this->mode);
            $program->process($this, $customer);
        }
    }

}

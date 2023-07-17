<?php

namespace core\models\company;

use core\helpers\company\CashbackHelper;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use core\models\user\User;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "{{%company_cashbacks}}".
 *
 * @property integer $id
 * @property integer $company_customer_id
 * @property integer $type
 * @property double $amount
 * @property integer $percent
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CompanyCustomer $companyCustomer
 * @property Order $order
 * @property User $createdBy
 * @property User $updatedBy
 */
class Cashback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cashbacks}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\company\query\CashbackQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\company\query\CashbackQuery(get_called_class());
    }

    /**
     * @param int $type
     * @param int $amount
     * @param int $percent
     * @param int $company_customer_id
     * @return Cashback
     */
    public static function add(int $type, int $amount, int $percent, int $company_customer_id): self
    {
        $model = new self();
        $model->type = $type;
        $model->amount = $amount;
        $model->percent = $percent;
        $model->company_customer_id = $company_customer_id;
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'amount', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'required'],
            [['company_customer_id', 'type', 'status', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['percent'], 'integer', 'min' => 0, 'max' => 100],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['company_customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::className(),
                'targetAttribute' => ['company_customer_id' => 'id']
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [
                ['updated_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['updated_by' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'type'                => Yii::t('app', 'Type'),
            'amount'              => Yii::t('app', 'Sum'),
            'percent'             => Yii::t('app', 'Percent'),
            'status'              => Yii::t('app', 'Status'),
            'created_by'          => Yii::t('app', 'Created by'),
            'updated_by'          => Yii::t('app', 'Updated by'),
            'created_at'          => Yii::t('app', 'Created at'),
            'updated_at'          => Yii::t('app', 'Updated at'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'value' => new Expression("NOW()")
            ],
            \yii\behaviors\BlameableBehavior::className(),
            [
                'class'                     => \yii2tech\ar\softdelete\SoftDeleteBehavior::className(),
                'softDeleteAttributeValues' => [
                    'status' => CashbackHelper::STATUS_DISABLED,
                ],
            ],
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if ($this->isIncome()) {
                $this->companyCustomer->addCashback($this->amount);
            }
            if ($this->isOutcome()) {
                $this->companyCustomer->subtractCashback($this->amount);
            }
            $this->companyCustomer->update(false);
        }
    }

    /**
     * @return bool
     */
    public function isIncome()
    {
        return $this->type == CashbackHelper::TYPE_IN;
    }

    /**
     * @return bool
     */
    public function isOutcome()
    {
        return $this->type == CashbackHelper::TYPE_OUT;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'company_customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \core\models\order\query\OrderQuery|\yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id'])
            ->viaTable('{{%order_cashbacks}}', ['company_cashback_id' => 'id']);
    }

    /**
     * @return mixed|null
     */
    public function getTypeName()
    {
        return CashbackHelper::getTypeLabel($this->type);
    }
}

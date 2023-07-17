<?php

namespace core\models\finance;

use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\query\CashflowQuery;
use core\models\finance\query\CashQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_company_cashes".
 *
 * @property integer $id
 * @property string $name
 * @property integer $company_id
 * @property integer $division_id
 * @property integer $type
 * @property integer $init_money
 * @property string $comments
 * @property integer $is_deletable
 * @property integer $status
 *
 * @property integer $balance
 * @property integer $income
 * @property integer $expense
 *
 * @property Division $division
 * @property Company $company
 * @property CompanyCashflow[] $cashflows
 */
class CompanyCash extends \yii\db\ActiveRecord
{
    const TYPE_CASH_BOX = 0;
    const TYPE_PAYMENT = 1;

    const IS_NOT_DELETABLE = 0;
    const IS_DELETABLE = 1;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    const BOX_PERIOD = '-30 day';

    /**
     * @param Division $division
     * @param string $name
     * @param integer $type
     * @param integer $init_money
     * @param string $comments
     * @param integer $is_deletable
     * @return CompanyCash
     */
    public static function add(Division $division, $name, $type, $init_money, $comments, $is_deletable)
    {
        $model = new CompanyCash();
        $model->populateRelation('division', $division);
        $model->company_id = $division->company_id;
        $model->name = $name;
        $model->type = $type;
        $model->init_money = $init_money;
        $model->comments = $comments;
        $model->is_deletable = $is_deletable;
        return $model;
    }

    /**
     * @param $comments
     * @param $name
     * @param int $init_money
     */
    public function edit($comments, $name, int $init_money)
    {
        $this->comments = $comments;
        $this->name = $name;
        $this->init_money = $init_money;
    }

    /**
     * @throws \Exception
     */
    public function disable()
    {
        $this->guardDisable();
        $this->status = self::STATUS_DISABLED;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_company_cashes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'default', 'value' => CompanyCash::TYPE_CASH_BOX],
            [['name', 'division_id', 'type'], 'required'],
            [['division_id', 'type', 'init_money', 'is_deletable'], 'integer'],
            [['comments'], 'string'],
            [['name'], 'string', 'max' => 255],
            ['init_money', 'default','value' => 0],
            ['status', 'default', 'value' => self::STATUS_ENABLED]

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'name' => Yii::t('app','Name'),
            'company_id' => Yii::t('app','Company'),
            'division_id' => Yii::t('app','Division'),
            'type' => Yii::t('app','Contractor Type'),
            'init_money' => Yii::t('app','Init Money'),
            'comments' => Yii::t('app','Comments'),
            'is_deletable' => Yii::t('app', 'Is Deletable')
        ];
    }

    /**
     * @return array
     */
    public static function getTypeLabels() {
        return [
            self::TYPE_CASH_BOX => Yii::t('app','Cash CashBox'),
            self::TYPE_PAYMENT => Yii::t('app','Cash PaymentAccount'),
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
     * @return \yii\db\ActiveQuery|CashflowQuery
     */
    public function getCashflows()
    {
        return $this->hasMany(CompanyCashflow::className(), ['cash_id' => 'id'])->active();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->init_money + $this->income - $this->expense;
    }

    /**
     * @param \DateTimeImmutable|null $dateTime
     * @return int
     */
    public function getIncome(\DateTimeImmutable $dateTime = null)
    {
        $query = $this->getCashflows()
                    ->active()
                    ->income()
            ->permittedDivisions();

        if ($dateTime) {
            $query->range($dateTime->format("Y-m-d"), $dateTime->modify("+1 month")->format("Y-m-d"));
        }

        return $query->sum('value');
    }

    /**
     * @param \DateTimeImmutable|null $dateTime
     * @return int
     */
    public function getExpense(\DateTimeImmutable $dateTime = null)
    {
        $query = $this->getCashflows()
            ->active()
            ->expense()
            ->permittedDivisions();

        if ($dateTime) {
            $query->range($dateTime->format("Y-m-d"), $dateTime->modify("+1 month")->format("Y-m-d"));
        }

        return $query->sum('value');
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var Company $company */
            if (isset($related['company']) && $company = $related['company']) {
                $company->save();
                $this->company_id = $company->id;
            } else {
                if (!$this->company_id) $this->company_id = Yii::$app->user->company_id;
            }
            if (isset($related['division']) && $division = $related['division']) {
                $division->save();
                $this->division_id = $division->id;
            }
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public static function map() {
        return ArrayHelper::map(self::find()->division()->active()->orderBy(['id' => SORT_DESC])->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CashQuery(get_called_class());
    }

    /**
     * Check if order status is ok to disable
     */
    private function guardDisable()
    {
        if (!$this->is_deletable) {
            throw new \Exception(Yii::t('app', 'Cannot delete cash'));
        }
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'type',
            'init_money',
            'comments',
            'is_deletable',
            'division_id',
            'status',
        ];
    }

}

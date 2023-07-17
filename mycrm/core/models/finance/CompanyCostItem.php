<?php

namespace core\models\finance;

use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\query\CostItemQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_company_cost_items".
 *
 * @property integer $id
 * @property integer $cost_item_type
 * @property string $name
 * @property integer $type
 * @property string $comments
 * @property integer $company_id
 * @property integer $is_deletable
 * @property integer $category_id
 *
 * @property Company $company
 * @property CompanyCostItemCategory $category
 */
class CompanyCostItem extends ActiveRecord
{
    const TYPE_INCOME = 0;
    const TYPE_EXPENSE = 1;
    const TYPE_ALL = 2; // For export only

    const COST_ITEM_TYPE_SERVICE = 1;
    const COST_ITEM_TYPE_SALARY = 2;
    const COST_ITEM_TYPE_PRODUCT_SALE = 3;
    const COST_ITEM_TYPE_DEBT_PAYMENT = 4;
    const COST_ITEM_TYPE_REFUND = 5;

    const COST_ITEM_TYPE_DEPOSIT_EXPENSE = 7;
    const COST_ITEM_TYPE_DEPOSIT_INCOME = 8;

    const COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER = 10;
    const COST_ITEM_TYPE_INCOME_CASH_TRANSFER = 11;

    /**
     * @param Company $company
     * @param string  $name
     * @param integer $type
     * @param string  $comments
     * @param         $cost_item_type
     * @param boolean $is_deletable
     * @param         $category_id
     *
     * @return CompanyCostItem
     */
    public static function add(Company $company, $name, $type, $comments, $cost_item_type, $is_deletable, $category_id)
    {
        $model = new CompanyCostItem();
        $model->populateRelation('company', $company);
        $model->name = $name;
        $model->type = $type;
        $model->comments = $comments;
        $model->cost_item_type = $cost_item_type;
        $model->is_deletable = $is_deletable;
        $model->category_id = $category_id;
        return $model;
    }

    /**
     * @param Company $company
     * @param string  $name
     * @param integer $type
     * @param string  $comments
     * @param integer $category_id
     */
    public function edit(Company $company, $name, $type, $comments, $category_id)
    {
        $this->populateRelation('company', $company);
        $this->name = $name;
        $this->type = $type;
        $this->comments = $comments;
        $this->category_id = $category_id;
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
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cost_items}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'name' => Yii::t('app','Name'),
            'type' => Yii::t('app','CostItem Type'),
            'comments' => Yii::t('app','Comments'),
            'company_id' => Yii::t('app','Company'),
        ];
    }

    /**
     * Returns translated name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->is_deletable ? $this->name : Yii::t('app', $this->name)
            . ($this->comments ? "<br><small>" . $this->comments . "</small>" : '');
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
    public function getCategory()
    {
        return $this->hasOne(CompanyCostItemCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCashflows()
    {
        return $this->hasMany(CompanyCashflow::className(), ['cost_item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisions()
    {
        return $this->hasMany(Division::className(), ['id' => 'division_id'])
            ->viaTable('{{%division_cost_items}}', ['cost_item_id' => 'id']);
    }

    /**
     * Assign current user company to the Model
     * @return bool
     */
    public function beforeValidate()
    {
        if($this->isNewRecord && !$this->company_id && isset(Yii::$app->user->identity)) {
            $this->company_id = \Yii::$app->user->identity->company_id;
        }
        return parent::beforeValidate();
    }

    /**
     * @inhertidoc
     */
    public function beforeDelete()
    {
        $this->unlinkAll('divisions', true);
    }

    /**
     * @return bool
     */
    public function canBeDeleted()
    {
        return $this->is_deletable ? $this->getCompanyCashflows()->exists() : false;
    }

    /**
     * Find Models related to the current company user
     * @return CostItemQuery
     */
    public static function find()
    {
        return new CostItemQuery(get_called_class());
    }

    /**
     * Map cost items
     * @param int $type filter by cost item type
     * @param null $cost_item_type
     * @param bool $excludeAuto
     * @return array
     */
    public static function map($type = null, $cost_item_type = null, bool $excludeAuto = false)
    {
        $all = self::find()->company()->permitted();
        $group = 'typeName';
        if ($type !== null) {
            $group = null;
        }

        $query = $all->andFilterWhere([
            'type'           => $type,
            'cost_item_type' => $cost_item_type
        ]);

        if ($excludeAuto) {
            $query->andWhere([
                'OR',
                [
                    'not in',
                    'cost_item_type',
                    [
                        self::COST_ITEM_TYPE_PRODUCT_SALE,
                        self::COST_ITEM_TYPE_REFUND,
                        self::COST_ITEM_TYPE_SERVICE,
                        self::COST_ITEM_TYPE_DEBT_PAYMENT,
                        self::COST_ITEM_TYPE_DEPOSIT_INCOME,
                        self::COST_ITEM_TYPE_DEPOSIT_EXPENSE,
                        self::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER,
                        self::COST_ITEM_TYPE_INCOME_CASH_TRANSFER,
                    ]
                ],
                ['cost_item_type' => null]
            ]);
        }

        return ArrayHelper::map($query->all(), 'id', 'fullName', $group);
    }

    /**
     * Filter all income/outcome
     * @return array
     */
    public static function mapFilter() {
        $extra = [
            -1 => Yii::t('app', 'CostItem All Income'),
            -2 => Yii::t('app', 'CostItem All Expense'),
        ];
        return $extra + ArrayHelper::map(self::find()->company()->permitted()->all(),
                                         'id', 'fullName', 'typeName');
    }

    /**
     * Gets localized cost item type.
     * @return string
     */
    public function getTypeName() {
        return $this->type === self::TYPE_INCOME ? Yii::t('app', 'CostItem Income')
                               : Yii::t('app', 'CostItem Expense');
    }

    /**
     * @return bool
     */
    public function isIncome(): bool
    {
        return $this->type == self::TYPE_INCOME;
    }

    /**
     * @return bool
     */
    public function isExpense(): bool
    {
        return $this->type == self::TYPE_EXPENSE;
    }

    /**
     * @return bool
     */
    public function isSale(): bool
    {
        return $this->cost_item_type == self::COST_ITEM_TYPE_PRODUCT_SALE;
    }

    /**
     * @return bool
     */
    public function isDepositTransaction(): bool
    {
        return $this->cost_item_type == self::COST_ITEM_TYPE_DEPOSIT_EXPENSE ||
            $this->cost_item_type == self::COST_ITEM_TYPE_DEPOSIT_INCOME;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name' => function (self $model) {
                return Yii::t('app', $model->name);
            },
            'type',
        ];
    }
}

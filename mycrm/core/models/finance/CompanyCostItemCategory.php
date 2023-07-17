<?php

namespace core\models\finance;

use core\models\company\Company;
use core\models\finance\query\CostItemCategoryQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%company_cost_item_categories}}".
 *
 * @property int     $id
 * @property string  $name
 * @property int     $company_id
 *
 * @property Company $company
 * @property CompanyCostItem[] $costItems
 */
class CompanyCostItemCategory extends ActiveRecord
{
    public $cost_items = [];

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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%company_cost_item_categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'company_id'], 'required'],
            [['company_id'], 'default', 'value' => null],
            [['company_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            ['cost_items', 'each', 'rule' => ['integer']],
            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::class,
                'targetAttribute' => ['company_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'name'       => Yii::t('app', 'Name'),
            'company_id' => Yii::t('app', 'Company ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name'
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->cost_items = $this->getCostItems()->select('id')->column();
    }

    public static function map()
    {
        return ArrayHelper::map(self::find()->company()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCostItems()
    {
        return $this->hasMany(CompanyCostItem::className(), ['category_id' => 'id']);
    }

    /**
     * Find Models related to the current company user
     * @return CostItemCategoryQuery()
     */
    public static function find()
    {
        return new CostItemCategoryQuery(get_called_class());
    }
}

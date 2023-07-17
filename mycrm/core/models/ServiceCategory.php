<?php

namespace core\models;

use common\components\HistoryBehavior;
use core\models\company\Company;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\query\DivisionServiceQuery;
use core\models\query\ServiceCategoryQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%service_categories}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $image_id
 * @property integer $parent_category_id
 * @property integer $order
 * @property integer $company_id
 * @property integer $type
 * @property integer $status
 *
 * @property Image $image
 * @property DivisionService $divisionServices
 * @property ServiceCategory $parentCategory
 * @property ServiceCategory[] $serviceCategories
 */
class ServiceCategory extends \yii\db\ActiveRecord
{
    const ROOT_BEAUTY = 2;
    const ROOT_STOMATOLOGY = 124;
    const ROOT_CLINIC = 14;

    const TYPE_CATEGORY_STATIC = 1;
    const TYPE_CATEGORY_DYNAMIC = 2;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            ['!type', 'default', 'value' => self::TYPE_CATEGORY_DYNAMIC],
            [
                ['parent_category_id'],
                'required',
                'when' => function () {
                    return $this->type == self::TYPE_CATEGORY_DYNAMIC;
                }
            ],
            [['image_id', 'parent_category_id', 'order', '!company_id', '!type', '!status'], 'integer'],
//            ['company_id', 'default', 'value' => Yii::$app->user->identity->company_id]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'image_id' => Yii::t('app', 'Image ID'),
            'parent_category_id' => Yii::t('app', 'Parent Category ID'),
            'order' => Yii::t('app', 'Order'),
        ];
    }

    /**
     * @deprecated
     * @return \yii\db\ActiveQuery|DivisionServiceQuery
     */
    public function getDivisionServices()
    {
        return $this->hasMany(DivisionService::className(), ['id' => 'division_service_id'])
            ->viaTable('{{%division_services_map}}', ['category_id' => 'id']);
    }

    /**
     * // to fetch service.For backward compatibility
     * @return \yii\db\ActiveQuery|DivisionServiceQuery
     */
    public function getServices()
    {
        return $this->hasMany(DivisionService::className(), ['id' => 'division_service_id'])
            ->viaTable('{{%division_services_map}}', ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentCategory()
    {
        return $this->hasOne(ServiceCategory::className(), ['id' => 'parent_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceCategories()
    {
        return $this->hasMany(ServiceCategory::className(), ['parent_category_id' => 'id']);
    }

    /**
     * Returns root categories
     * @return ServiceCategory[]
     */
    public static function getRootCategories()
    {
        return ServiceCategory::find()->root()->enabled()->staticType()->all();
    }

    /**
     * @param $categories
     * @return array|ServiceCategory[]|\yii\db\ActiveRecord[]
     */
    public static function getStaticCategories($categories)
    {
        $query = ServiceCategory::find()->staticType();
        if ($categories) {
            $query->andWhere(['{{%service_categories}}.parent_category_id' => $categories])->innerJoinWith([
                'serviceCategories sc' => function (ServiceCategoryQuery $query) {
                    return $query->enabled()->innerJoinWith([
                        'divisionServices' => function (DivisionServiceQuery $query) {
                            return $query->permitted();
                        }
                    ], false);
                }
            ], false);
        }
        return $query->all();
    }

    /**
     * @param null $categories
     * @param int|null $company_id
     * @return array|ServiceCategory[]|\yii\db\ActiveRecord[]
     */
    public static function getDynamicCategories($categories = null, int $company_id = null)
    {
        $query = ServiceCategory::find()->dynamicType()->byCompanyId($company_id)->enabled();
        if ($categories) {
            $query->andWhere(['parent_category_id' => $categories]);
        }
        return $query->all();
    }

    /**
     * Returns child categories
     * @return ServiceCategory[]
     */
    public function getChildCategories()
    {
        $models = ServiceCategory::find()
            ->orderBy(['order' => SORT_ASC])
            ->where(['parent_category_id' => $this->id, 'type' => ServiceCategory::TYPE_CATEGORY_STATIC])
            ->all();
        $result = [];
        foreach($models as $model)
        {
            $services = [];
            foreach ($model->serviceCategories as $childCategory)
            {
                /** @var ServiceCategory $service */
                $services[] = [
                    'id'             => $childCategory->id,
                    'name'           => $childCategory->name,
                    'division_count' => $childCategory->getDivisionsCount(),
                ];
            }

            /* @var $model ServiceCategory */
            $image_path = "";
            if (!empty($model->image_id))
            {
                $image_path = \Yii::$app->params['crm_host'] . $model->image->getPath();
            }

            $data = [
                'id' => $model->id,
                'name' => $model->name,
                'image' => $image_path,
                'services' => $services,
            ];

            array_push($result, $data);
        }
        return $result;
    }

    /**
     * @param int|null $company_id
     * @return array|static[]
     */
    public static function getCompanyCategories(int $company_id = null)
    {
        $rootCategories = [];
        $divisions = Division::find()->select('category_id')->company($company_id)->enabled()->asArray()->all();
        if ($divisions) {
            $rootCategories = ArrayHelper::getColumn($divisions, 'category_id');
            $categories = array_merge(self::getStaticCategories($rootCategories),
                self::getDynamicCategories($rootCategories, $company_id));
            ArrayHelper::multisort($categories, 'name');
            return $categories;
        }
        return ServiceCategory::findAll(['id' => $rootCategories]);
    }

    /**
     * @return bool
     */
    public function disable()
    {
        $this->status = self::STATUS_DISABLED;
        return $this->save();
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'                 => 'id',
            'name'               => 'name',
            'division_count'     => function (self $model) {
                return $model->getDivisionsCount();
            },
            "parent_category_id" => function (self $model) {
                return intval($model->parent_category_id);
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'subcategories' => function (self $model) {
                return $model->getChildCategories();
            },
            'services'      => 'divisionServices'
        ];
    }

    /**
     * @return array
     */
    public static function map()
    {
        return ArrayHelper::map(self::getCompanyCategories(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new ServiceCategoryQuery(get_called_class());
    }

    /**
     * Returns number of divisions in category
     *
     * @return integer
     */
    public function getDivisionsCount()
    {
        return DivisionService::find()
            ->deleted(false)
            ->joinWith([
                'categories',
                'divisions.company',
            ], false)
            ->andWhere([
                '{{%companies}}.publish'                     => Company::PUBLISH_TRUE,
                '{{%service_categories}}.parent_category_id' => $this->id,
            ])
            ->count('DISTINCT {{%divisions}}.id');
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

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->type == self::TYPE_CATEGORY_STATIC;
    }

    /**
     * @return bool
     */
    public function isDynamic()
    {
        return $this->type == self::TYPE_CATEGORY_DYNAMIC;
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return ArrayHelper::map(
            self::find()->joinWith('parentCategory pa')->andWhere([
                '{{%service_categories}}.type'       => ServiceCategory::TYPE_CATEGORY_DYNAMIC,
                '{{%service_categories}}.company_id' => \Yii::$app->user->identity->company_id
            ])->enabled()->asArray()->orderBy('pa.name ASC, {{%service_categories}}.name ASC')->all(),
            'id',
            'name',
            'parentCategory.name'
        );
    }

}

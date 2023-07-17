<?php

namespace core\models\medCard;

use core\models\query\CommentTemplateCategoryQuery;
use core\models\ServiceCategory;
use core\models\company\CompanyPosition;
use core\rbac\IRbacPermissions;
use core\rbac\RbacPermissions;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%med_card_comment_categories}}".
 *
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $parent_id
 * @property integer                  $order
 * @property integer                  $service_category_id
 *
 * @property MedCardCommentCategory   $parent
 * @property ServiceCategory          $serviceCategory
 * @property MedCardCommentCategory[] $categories
 * @property MedCardComment[]         $templates
 * @property CompanyPosition[]        $companyPositions
 */
class MedCardCommentCategory
    extends \yii\db\ActiveRecord
    implements IRbacPermissions
{
    use RbacPermissions;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_comment_categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'service_category_id'], 'required'],
            [['parent_id', 'service_category_id', 'order'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [
                ['parent_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => MedCardCommentCategory::className(),
                'targetAttribute' => ['parent_id' => 'id']
            ],
            [
                ['service_category_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => ServiceCategory::className(),
                'targetAttribute' => ['service_category_id' => 'id']
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
            'name'                => Yii::t('app', 'Name'),
            'parent_id'           => Yii::t('app', 'Parent'),
            'order'           => Yii::t('app', 'Order'),
            'service_category_id' => Yii::t('app', 'Service Category'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(MedCardCommentCategory::className(),
            ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceCategory()
    {
        return $this->hasOne(ServiceCategory::className(),
            ['id' => 'service_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(MedCardCommentCategory::className(),
            ['parent_id' => 'id'])->orderBy('name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplates()
    {
        return $this->hasMany(MedCardComment::className(),
            ['category_id' => 'id'])->orderBy('comment');
    }

    /**
     * @return \core\models\company\query\CompanyPositionQuery
     */
    public function getCompanyPositions()
    {
        /** @var \core\models\company\query\CompanyPositionQuery $query */
        $query = $this->hasMany(CompanyPosition::className(),
            ['id' => 'company_position_id'])
            ->viaTable(
                '{{%company_position_med_cart_comment_category_map}}',
                ['med_card_comment_category_id' => 'id']
            );
        return $query->notDeleted();
    }


    /**
     * Returns key name for permissions
     *
     * @return string
     */
    public static function getPermissionKey()
    {
        return 'commentTemplateCategory';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CommentTemplateCategoryQuery(get_called_class());
    }

    /**
     * Returns mapped list of company positions
     *
     * @return MedCardCommentCategory[]
     */
    public static function mappedList()
    {
        return ArrayHelper::map(self::find()->orderBy('order')->all(), "id", "name");
    }
}

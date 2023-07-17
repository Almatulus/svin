<?php

namespace core\models\medCard;

use core\rbac\IRbacPermissions;
use core\rbac\RbacPermissions;
use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%med_card_comments}}".
 *
 * @property integer                $id
 * @property string                 $comment
 * @property integer                $category_id
 *
 * @property MedCardCommentCategory $category
 * @property MedCardDiagnosis[]     $diagnoses
 */
class MedCardComment extends ActiveRecord implements IRbacPermissions
{
    use RbacPermissions;

    public function behaviors()
    {
        return [
            [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'diagnosis_ids' => 'diagnoses'
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_comments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment'], 'string'],
            [['category_id'], 'required'],
            [['category_id'], 'integer'],
            [['diagnosis_ids'], 'each', 'rule' => ['integer']],
            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => MedCardCommentCategory::className(),
                'targetAttribute' => ['category_id' => 'id'
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'comment' => Yii::t('app', 'Comment'),
            'category_id' => Yii::t('app', 'Category ID'),
            'diagnosis_ids' => Yii::t('app', 'Diagnoses'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(MedCardCommentCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @reutrn \yii\db\ActiveQuery
     */
    public function getDiagnoses()
    {
        return $this->hasMany(MedCardDiagnosis::className(), ['id' => 'diagnosis_id'])
            ->viaTable('{{%med_card_comment_diagnosis_map}}', ['comment_template_id' => 'id']);
    }

    /**
     * Returns key name for permissions
     *
     * @return string
     */
    public static function getPermissionKey()
    {
        return 'commentTemplate';
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'comment'     => 'comment',
            'category_id' => 'category_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'diagnoses' => 'diagnoses',
        ];
    }
}

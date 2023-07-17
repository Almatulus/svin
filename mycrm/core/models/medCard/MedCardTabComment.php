<?php

namespace core\models\medCard;

use core\models\medCard\MedCardCommentCategory;
use core\models\medCard\MedCardTab;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%med_card_tab_comments}}".
 *
 * @property integer                $id
 * @property integer                $category_id
 * @property integer                $med_card_tab_id
 * @property string                 $comment
 * @property string                 $created_at
 * @property string                 $updated_at
 *
 * @property MedCardCommentCategory $category
 * @property MedCardTab             $medCardTab
 */
class MedCardTabComment extends \yii\db\ActiveRecord
{
    /**
     * Returns new MedCardTabComment model
     *
     * @param MedCardTab             $medCardTab
     * @param MedCardCommentCategory $category
     * @param string                 $comment
     *
     * @return MedCardTabComment
     */
    public static function add(MedCardTab $medCardTab, MedCardCommentCategory $category, string $comment): MedCardTabComment
    {
        $model = new MedCardTabComment();
        $model->populateRelation('medCardTab', $medCardTab);
        $model->populateRelation('category', $category);
        $model->comment = $comment;
        return $model;
    }

    /**
     * Changes comment string
     * @param string $comment
     */
    public function changeComment(string $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Removes comment string
     */
    public function clearComment()
    {
        $this->comment = '';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_tab_comments}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardTab()
    {
        return $this->hasOne(MedCardTab::className(), ['id' => 'med_card_tab_id']);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'=> 'id',
            'comment' => 'comment',
            'category_id' => 'category_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'med_card_tab_id' => 'med_card_tab_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $related = $this->getRelatedRecords();

            /** @var MedCardTab $medCardTab */
            if (isset($related['medCardTab']) && $medCardTab = $related['medCardTab']) {
                $medCardTab->save();
                $this->med_card_tab_id = $medCardTab->id;
            }

            /** @var MedCardCommentCategory $category */
            if (isset($related['category']) && $category = $related['category']) {
                $category->save();
                $this->category_id = $category->id;
            }

            return true;
        }
        return false;
    }
}

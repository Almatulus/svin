<?php

namespace core\models\medCard;

use core\models\company\Company;
use core\repositories\exceptions\AlreadyExistsException;
use core\repositories\exceptions\EmptyVariableException;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%med_card_company_comments}}".
 *
 * @property integer                $id
 * @property integer                $company_id
 * @property integer                $category_id
 * @property string                 $comment
 *
 * @property Company                $company
 * @property MedCardCommentCategory $category
 */
class MedCardCompanyComment extends ActiveRecord
{
    const HEADLINE_PATTERN = '/^[\d+\,\ ]*:/';

    /**
     * @param Company                $company
     * @param MedCardCommentCategory $category
     * @param string                 $comment
     *
     * @return MedCardCompanyComment
     */
    public static function add(
        Company $company,
        MedCardCommentCategory $category,
        $comment
    ) {
        $clear_comment = self::clearComment($comment);
        self::guardEmptyComment($clear_comment);
        self::guardExist($company, $category, $clear_comment);

        $model           = new MedCardCompanyComment();
        $model->company  = $company;
        $model->category = $category;
        $model->comment  = $clear_comment;

        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_company_comments}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'company_id'  => Yii::t('app', 'Company ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'comment'     => Yii::t('app', 'Comment'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(MedCardCommentCategory::className(),
            ['id' => 'category_id']);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'company_id'  => 'company_id',
            'category_id' => 'category_id',
            'comment'     => 'comment',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::className(),
                'relations' => ['company', 'category'],
            ],
        ];
    }

    /**
     * @param string $comment
     *
     * @return string
     */
    public static function clearComment(string $comment): string
    {
        $comment = trim($comment);

        preg_match(
            MedCardCompanyComment::HEADLINE_PATTERN,
            $comment,
            $matches
        );

        if ( ! empty($matches)) {
            $comment = trim(str_replace($matches[0], '', $comment));
        }

        return $comment;
    }

    /**
     * @param Company                $company
     * @param MedCardCommentCategory $category
     * @param string                 $comment
     */
    private static function guardExist(
        Company $company,
        MedCardCommentCategory $category,
        string $comment
    ) {
        $duplicate = self::find()
            ->andWhere([
                'company_id'  => $company->id,
                'comment'     => $comment,
                'category_id' => $category->id,
            ])->exists();
        if ($duplicate) {
            throw new AlreadyExistsException('Comment already exists');
        }

        $duplicate = MedCardComment::find()
            ->andWhere([
                'comment'     => $comment,
                'category_id' => $category->id,
            ])
            ->exists();
        if ($duplicate) {
            throw new AlreadyExistsException('Comment already exists');
        }
    }

    /**
     * @param string $comment
     */
    private static function guardEmptyComment(string $comment)
    {
        if (empty($comment)) {
            throw new EmptyVariableException('Comment is empty');
        }
    }
}

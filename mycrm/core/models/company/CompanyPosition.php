<?php

namespace core\models\company;

use core\models\company\query\CompanyPositionQuery;
use core\models\document\DocumentForm;
use core\models\Staff;
use core\models\medCard\MedCardCommentCategory;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%company_positions}}".
 *
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $company_id
 * @property integer                  $position_id
 * @property string                   $description
 * @property string                   $deleted_time
 *
 * @property Company                  $company
 * @property Staff[]                  $staffs
 * @property MedCardCommentCategory[] $medCardCommentCategories
 * @property DocumentForm[]           $documentForms
 *
 * @mixin \yii2tech\ar\softdelete\SoftDeleteBehavior
 */
class CompanyPosition extends ActiveRecord
{
    const STRING_DELIMITER = ' | ';

    /**
     * @param string $name
     * @param string $description
     * @param Company $company
     * @param integer $position_id
     * @return CompanyPosition
     */
    public static function add(
        $name,
        $description,
        Company $company,
        $position_id = null
    ) {
        $model = new CompanyPosition();
        $model->populateRelation('company', $company);
        $model->name        = $name;
        $model->description = $description;
        $model->position_id = $position_id;

        return $model;
    }

    /**
     * @param string $name
     * @param string $description
     */
    public function edit($name, $description)
    {
        $this->name        = $name;
        $this->description = $description;
    }

    /**
     * Returns behaviors for this model.
     * @return array of behaviors
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii2tech\ar\softdelete\SoftDeleteBehavior::className(),
                'softDeleteAttributeValues' => [
                    'deleted_time' => date('Y-m-d H:i:s'),
                ],
            ],
        ];
    }

    /**
     * @param MedCardCommentCategory[] $categories
     */
    public function setCategories($categories)
    {
        $this->populateRelation('medCardCommentCategories', $categories);
    }

    /**
     * @param DocumentForm[] $documentForms
     */
    public function setDocumentFormsRelation($documentForms)
    {
        $this->populateRelation('documentForms', $documentForms);
    }

    /**
     * @param MedCardCommentCategory[] $categories
     */
    public function bind($categories)
    {
        foreach ($categories as $category) {
            $this->link('medCardCommentCategories', $category);
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_positions}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'name'        => Yii::t('app', 'Name'),
            'company_id'  => Yii::t('app', 'Company ID'),
            'description' => Yii::t('app', 'Description'),
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
    public function getStaffs()
    {
        return $this->hasMany(Staff::className(), ['id' => 'staff_id'])
            ->viaTable('{{%staff_company_position_map}}', ['company_position_id' => 'id']);
    }

    /**
     * Returns list company positions I work in
     *
     * @return CompanyPosition[]
     */
    public static function getOwnCompanyPositions()
    {
        return CompanyPosition::find()
            ->notDeleted()
            ->company(Yii::$app->user->identity->company_id)
            ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardCommentCategories()
    {
        return $this->hasMany(MedCardCommentCategory::className(),
            ['id' => 'med_card_comment_category_id'])
                    ->viaTable(
                        '{{%company_position_med_cart_comment_category_map}}',
                        ['company_position_id' => 'id']
                    );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForms()
    {
        return $this->hasMany(DocumentForm::className(), [
            'id' => 'document_form_id'
        ])->viaTable('{{%document_form_company_position_map}}', [
            'company_position_id' => 'id'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
//        $this->unlinkAll("staffs"/*, true*/);
//        $this->unlinkAll("medCardCommentCategories"/*, true*/);

        return parent::beforeDelete();
    }

    /**
     * Returns mapped list of company positions
     *
     * @return CompanyPosition[]
     */
    public static function mappedList()
    {
        return ArrayHelper::map(self::getOwnCompanyPositions(), "id", "name");
    }

    /**
     * @inheritdoc
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave(
            $insert,
            $changedAttributes
        );

        $related = $this->getRelatedRecords();
        /** @var MedCardCommentCategory[] $medCardCommentCategories */
        if (isset($related['medCardCommentCategories'])
            && $medCardCommentCategories = $related['medCardCommentCategories']
        ) {
            $this->unlinkAll('medCardCommentCategories', true);
            foreach ($medCardCommentCategories as $category) {
                $this->link('medCardCommentCategories', $category);
            }
        }

        /** @var MedCardCommentCategory[] $medCardCommentCategories */
        if (isset($related['documentForms'])) {
            $documentForms = $related['documentForms']; // TODO to be able to save with empty documentForms
            $this->unlinkAll('documentForms', true);
            foreach ($documentForms as $documentForm) {
                $this->link('documentForms', $documentForm);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CompanyPositionQuery(get_called_class());
    }

    public function fields()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'description' => 'description'
        ];
    }

    public function extraFields()
    {
        return [
            'staffs' => 'staffs',
            'commentCategories' => 'medCardCommentCategories',
            'documentForms' => 'documentForms',
        ];
    }
}

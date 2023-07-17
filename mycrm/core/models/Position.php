<?php

namespace core\models;

use core\models\document\DocumentForm;
use core\models\query\PositionQuery;
use Yii;

/**
 * This is the model class for table "crm_positions".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $service_category_id
 * @property string $deleted_time
 *
 * @property DocumentForm[] $documentForms
 * @property ServiceCategory $serviceCategory
 *
 * @mixin \yii2tech\ar\softdelete\SoftDeleteBehavior
 */
class Position extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_positions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'service_category_id'], 'required'],
            [['description'], 'string'],
            [['service_category_id'], 'default', 'value' => null],
            [['service_category_id'], 'integer'],
            [['deleted_time'], 'safe'],
            [['name'], 'string', 'max' => 511],
            [['service_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceCategory::className(), 'targetAttribute' => ['service_category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'service_category_id' => Yii::t('app', 'Service Category'),
            'deleted_time' => 'Deleted Time',
        ];
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
     * @param string  $name
     * @param string  $description
     *
     * @return Position
     */
    public static function add($name, $description)
    {
        $model = new Position();
        $model->name        = $name;
        $model->description = $description;

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
     * @param DocumentForm[] $documentForms
     */
    public function setDocumentFormsRelation($documentForms)
    {
        $this->populateRelation('documentForms', $documentForms);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForms()
    {
        return $this->hasMany(DocumentForm::className(), [
            'id' => 'document_form_id'
        ])->viaTable('crm_document_form_position_map', [
            'position_id' => 'id'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceCategory()
    {
        return $this->hasOne(ServiceCategory::className(), ['id' => 'service_category_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave(
            $insert,
            $changedAttributes
        );

        $related = $this->getRelatedRecords();

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
        return new PositionQuery(get_called_class());
    }
}

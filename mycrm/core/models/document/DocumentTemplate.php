<?php

namespace core\models\document;

use core\models\user\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%document_templates}}".
 *
 *
 * @property int $document_form_id
 * @property string $name
 * @property string $values
 * @property int $created_by
 * @property string $created_at
 *
 * @property DocumentForm $documentForm
 * @property User $creator
 */
class DocumentTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_templates}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_form_id', 'name', 'created_by', 'created_at'], 'required'],
            [['document_form_id', 'created_by'], 'default', 'value' => null],
            [['document_form_id', 'created_by'], 'integer'],
            [['values'], 'string'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [
                ['document_form_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentForm::className(),
                'targetAttribute' => ['document_form_id' => 'id']
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => Yii::t('app', 'ID'),
            'document_form_id' => Yii::t('app', 'Document Form ID'),
            'name'             => Yii::t('app', 'Name'),
            'values'           => Yii::t('app', 'Values'),
            'created_by'       => Yii::t('app', 'Created By'),
            'created_at'       => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value'      => new Expression("NOW()")
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForm()
    {
        return $this->hasOne(DocumentForm::className(), ['id' => 'document_form_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'created_at',
            'created_by',
            'document_form_id',
            'name',
            'dentalCard' => function (self $model) {
                return json_decode($model->values, true)['dentalCard'] ?? [];
            },
            'values'     => function (self $model) {
                $values = json_decode($model->values, true);

                if (isset($values['dentalCard'])) {
                    unset($values['dentalCard']);
                }

                $data = [];
                foreach ($values as $key => $value) {
                    $data[] = ['key' => $key, 'value' => $value];
                }
                return $data;
            }
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'documentForm'
        ];
    }
}

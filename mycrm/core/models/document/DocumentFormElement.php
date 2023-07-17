<?php

namespace core\models\document;

use core\helpers\AppHelper;
use Yii;

/**
 * This is the model class for table "{{%document_form_element}}".
 *
 * @property integer $id
 * @property integer $order
 * @property integer $raw_id
 * @property string $label
 * @property string $key
 * @property integer $type
 * @property string $options
 * @property string $search_url
 * @property string $depends_on
 * @property integer $document_form_id
 * @property integer $document_form_group_id
 * @property boolean $is_comment
 *
 * @property DocumentForm $documentForm
 * @property DocumentFormGroup $group
 */
class DocumentFormElement extends \yii\db\ActiveRecord
{
    const TYPE_TEXT_INPUT = 1;
    const TYPE_SELECT = 2;
    const TYPE_CHECKBOX = 3;
    const TYPE_RADIOLIST = 4;
    const TYPE_TEXT = 5;
    const TYPE_DATE = 6;
    const TYPE_CHECKBOX_LIST = 7;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_form_elements}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order', 'type', 'document_form_id', 'document_form_group_id', 'raw_id'], 'integer'],
            [['label', 'type', 'document_form_id'], 'required'],
            [['options', 'search_url', 'depends_on'], 'string'],
            [['label', 'key'], 'string', 'max' => 255],
            ['is_comment', 'boolean'],
            [
                ['document_form_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentForm::className(),
                'targetAttribute' => ['document_form_id' => 'id']
            ],
            [
                ['document_form_group_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentFormGroup::className(),
                'targetAttribute' => ['document_form_group_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => Yii::t('app', 'ID'),
            'order'                  => Yii::t('app', 'Order'),
            'label'                  => Yii::t('app', 'Label'),
            'raw_id'                 => Yii::t('app', 'Raw'),
            'key'                    => Yii::t('app', 'Key'),
            'type'                   => Yii::t('app', 'Type'),
            'options'                => Yii::t('app', 'Options'),
            'document_form_id'       => Yii::t('app', 'Document Form ID'),
            'document_form_group_id' => Yii::t('app', 'Document Form Group ID'),
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
    public function getGroup()
    {
        return $this->hasOne(DocumentFormGroup::className(), ['id' => 'document_form_group_id']);
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT_INPUT    => 'text input',
            self::TYPE_SELECT        => 'selectbox',
            self::TYPE_CHECKBOX      => 'checkbox',
            self::TYPE_RADIOLIST     => 'radio list',
            self::TYPE_TEXT          => 'text',
            self::TYPE_DATE          => 'date',
            self::TYPE_CHECKBOX_LIST => 'checkbox list',
        ];
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        $types = self::getTypes();

        if ( ! isset($types[$this->type])) {
            return null;
        }

        return $types[$this->type];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'group_id' => 'document_form_group_id',
            'label',
            'key',
            'raw_id',
            'order',
            'options'  => 'decodedOptions',
            'type'     => function (self $model) {
                return self::getTypes()[$model->type] ?? null;
            },
            'depends_on',
            'search_url'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'group'
        ];
    }

    /**
     * @return array
     */
    public function getDecodedOptions()
    {
        return $this->options ? AppHelper::arrayFromPg($this->options) : null;
    }

    /**
     * @return array
     */
    public function generateRules()
    {
        switch ($this->type) {
            case self::TYPE_TEXT:
            case self::TYPE_TEXT_INPUT:
                return [[$this->key, 'string'], [$this->key, 'trim']];
            case self::TYPE_SELECT:
            case self::TYPE_RADIOLIST:
                if (!empty($this->search_url)) {
                    return [[$this->key, 'string']];
                }
                return [[$this->key, 'in', 'range' => array_keys($this->getDecodedOptions())]];
            case self::TYPE_CHECKBOX:
                return [[$this->key, 'boolean']];
            case self::TYPE_DATE:
                return [[$this->key, 'date', 'format' => 'php:Y-m-d']];
            case self::TYPE_CHECKBOX_LIST:
                return [
                    [$this->key, 'each', 'rule' => ['in', 'range' => array_keys($this->getDecodedOptions())]],
                    [
                        $this->key,
                        'filter',
                        'skipOnError' => true,
                        'filter'      => function ($data) {
                            if (is_array($data)) {
                                return json_encode($data);
                            }
                            return $data;
                        }
                    ]
                ];
            default:
                return [[]];
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formatValue($value)
    {
        if ($this->type == self::TYPE_CHECKBOX_LIST && !empty($value)) {
            return json_decode($value);
        }
        return $value;
    }

    /**
     * @return bool
     */
    public function isTextField()
    {
        return $this->type == self::TYPE_TEXT;
    }
}

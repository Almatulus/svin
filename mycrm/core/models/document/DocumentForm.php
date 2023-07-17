<?php

namespace core\models\document;

use core\models\company\CompanyPosition;
use Yii;

/**
 * This is the model class for table "{{%med_document_form}}".
 *
 * @property integer $id
 * @property string $name
 * @property boolean $has_dental_card
 * @property boolean $has_services
 * @property string $doc_path
 * @property boolean $enabled
 * @property DocumentFormElement[] $elements
 * @property DocumentFormGroup[] $groups
 *
 * @property CompanyPosition[] $companyPositions
 */
class DocumentForm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_forms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['has_dental_card', 'has_services', 'enabled'], 'boolean'],
            [['name', 'doc_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'enabled'         => Yii::t('app', 'Enabled'),
            'name'            => Yii::t('app', 'Name'),
            'has_dental_card' => Yii::t('app', 'Has Dental Card'),
            'has_services'    => Yii::t('app', 'Has Services'),
            'doc_path'        => Yii::t('app', 'Doc Path'),
        ];
    }

    /**
     * @inheritdoc
     * @return \core\models\document\query\DocumentFormQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\document\query\DocumentFormQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(DocumentFormGroup::class, ['document_form_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElements()
    {
        return $this->hasMany(DocumentFormElement::class, ['document_form_id' => 'id']);
    }

    /**
     * @return array
     */
    public function getStructuredElements()
    {
        /** @var DocumentFormElement[] $elements */
        $elements = $this->getElements()
            ->with('group')
            ->orderBy(['order' => SORT_ASC])
            ->all();

        $group_row = [];
        $result = [];
        foreach ($elements as $element) {
            if ($element->document_form_group_id) {
                if ( ! isset($group_row[$element->document_form_group_id])) {
                    $group_row[$element->document_form_group_id] = $element->order;
                }
                $row_id = $group_row[$element->document_form_group_id];
                $result[$row_id]["label"] = $element->group->label;
                $result[$row_id]["group_id"] = $element->group->id;
                $result[$row_id]["items"][$element->raw_id][] = $element->toArray();
            } else {
                $result[$element->order] = $element->toArray();
            }
        }

        return array_values($result);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyPositions()
    {
        return $this->hasMany(CompanyPosition::className(), [
            'id' => 'company_position_id'
        ])->viaTable('{{%document_form_company_position_map}}', [
            'document_form_id' => 'id'
        ]);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'has_dental_card',
            'has_services',
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'elements' => 'structuredElements'
        ];
    }

    /**
     * @return array|DocumentFormElement[]
     */
    public function getInputElements()
    {
        return array_filter($this->elements, function (DocumentFormElement $element) {
            return !$element->isTextField();
        });
    }
}

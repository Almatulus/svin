<?php

namespace core\forms\medCard;

use core\models\medCard\MedCardDiagnosis;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * @property integer $diagnosis_id
 * @property array $comments
 * @property array $teeth
 * @property array $services
 */
class MedCardTabForm extends Model
{
    public $diagnosis_id;
    public $comments;
    public $teeth;
    public $services;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['diagnosis_id', 'integer'],
            ['diagnosis_id', 'exist', 'targetClass' => MedCardDiagnosis::class, 'targetAttribute' => 'id'],
            [
                'teeth',
                'filter',
                'filter' => function ($data) {
                    if (is_array($data)) {
                        return array_filter($data, function ($tooth) {
                            return ! empty($tooth['diagnosis_id']);
                        });
                    }

                    return $data;
                }
            ],
            ['comments', 'validateComments'],
            ['teeth', 'validateTeeth'],
            ['services', 'validateServices'],
        ];
    }

    public function validateServices($attribute, $params)
    {
        foreach ($this->services as $data) {
            $form                      = new MedCardTabServiceForm();
            $form->quantity            = $data['quantity'];
            $form->division_service_id = $data['division_service_id'];
            $form->discount            = $data['discount'];
            $form->price               = $data['price'];
            if ( ! $form->validate()) {
                $this->addError($attribute, Yii::t('app', 'Service error'));
            }
        }
    }

    public function validateComments($attribute, $params)
    {
        foreach ($this->comments as $comment_template_category_id => $comment) {
            $commentForm          = new MedCardTabCommentManageForm();
            $commentForm->comment = $comment;
            $commentForm->comment_template_category_id
                = $comment_template_category_id;
            if ( ! $commentForm->validate()) {
                $error_message = Yii::t('app', 'Comment error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($commentForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    public function validateTeeth($attribute, $params)
    {
        foreach ($this->teeth as $teeth_num => $tooth) {
            if ( ! empty($tooth['diagnosis_id'])) {
                continue;
            }

            $toothForm               = new MedCardToothForm();
            $toothForm->number       = $teeth_num;
            $toothForm->diagnosis_id = $tooth['diagnosis_id'];
            $toothForm->mobility     = $tooth['mobility'] ?? null;
            if ( ! $toothForm->validate()) {
                $error_message = Yii::t('app', 'Teeth error');
                if (YII_DEBUG) {
                    $error_message .= Json::encode($toothForm->errors);
                }
                $this->addError($attribute, $error_message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'diagnosis_id' => Yii::t('app', 'Diagnosis'),
            'comments'     => Yii::t('app', 'Comments'),
            'tooth'        => Yii::t('app', 'Teeth'),
            'services'     => Yii::t('app', 'Services'),
        ];
    }

    public function formName()
    {
        return 'MedCard';
    }
}

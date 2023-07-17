<?php

use core\models\document\DocumentFormElement;
use core\models\document\DocumentFormGroup;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\document\forms\ElementsForm */

$this->title = $model->getName();
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-image"></span> {link}</li>',
    'label'    => Yii::t('app', 'Document Forms'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Elements');
?>

<div class="document-form-elements">

    <h3><?= Html::encode($this->title) ?></h3>

    <div class="document-form-elements-form">

        <?php $form = ActiveForm::begin([
            'enableAjaxValidation'   => true,
            'enableClientValidation' => false
        ]); ?>

        <?= $form->field($model, 'elements')->widget(MultipleInput::className(), [
            'id'                => 'document-form-elements',
            'allowEmptyList'    => true,
            'addButtonPosition' => MultipleInput::POS_HEADER,
            'columns'           => [
                [
                    'name' => 'id',
                    'type' => MultipleInputColumn::TYPE_HIDDEN_INPUT
                ],
                [
                    'name'             => 'label',
                    'enableError'      => true,
                    'title'            => 'Label',
                    'attributeOptions' => [
                        'enableAjaxValidation' => true,
                    ]
                ],
                [
                    'name'        => 'key',
                    'enableError' => true,
                    'title'       => 'Key'
                ],
                [
                    'name'        => 'type',
                    'enableError' => true,
                    'type'        => MultipleInputColumn::TYPE_DROPDOWN,
                    'items'       => DocumentFormElement::getTypes(),
                    'title'       => 'Type'
                ],
                [
                    'name'         => 'order',
                    'enableError'  => true,
                    'title'        => 'Order',
                    'defaultValue' => 1
                ],
                [
                    'name'         => 'raw_id',
                    'enableError'  => true,
                    'title'        => 'Raw',
                    'defaultValue' => 1
                ],
                [
                    'name'        => 'search_url',
                    'enableError' => true,
                    'title'       => 'Search Url'
                ],
                [
                    'name'        => 'depends_on',
                    'enableError' => true,
                    'title'       => 'Depends on'
                ],
                [
                    'name'        => 'document_form_group_id',
                    'enableError' => true,
                    'title'       => 'Group',
                    'type'        => MultipleInputColumn::TYPE_DROPDOWN,
                    'items'       => \yii\helpers\ArrayHelper::map(
                        DocumentFormGroup::findAll(['document_form_id' => $model->id]),
                        'id',
                        'label'
                    ),
                    'options'     => [
                        'prompt' => \Yii::t('app', 'Select Group')
                    ]
                ],
                [
                    'name'    => 'options',
                    'title'   => 'Options',
                    'type'    => MultipleInput::className(),
                    'options' => [
                        'columns' => [
                            [
                                'enableError' => true,
                                'name'        => 'label',
                            ],
                            [
                                'name' => 'key',
                                'type' => MultipleInputColumn::TYPE_HIDDEN_INPUT
                            ],
                        ]
                    ]
                ]
            ]
        ]);
        ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'),
                ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

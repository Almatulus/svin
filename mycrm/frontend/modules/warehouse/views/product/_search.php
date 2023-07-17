<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\ProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-search">
    <div class="column_row row buttons-row">
        <div class="col-sm-5">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'options' => ['data-pjax' => true ],
            ]); ?>
                <div class="product-search-name">
                    <?php
                    echo Html::activeTextInput($model, 'name', ['class' => 'right_space', 'placeholder' => Yii::t('app', 'Find product')]);
                    echo Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']);
                    ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-sm-7 right-buttons">
            <div class="customer-actions inline_block">
                <div class="dropdown">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                        Действия <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export fetched to Excel'),
                                'export?' . Yii::$app->request->queryString
                            ) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export all to Excel'),
                                'export?all=true'
                            ) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Download template'), '#', ['id' => 'js-download-template']) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Import from Excel'), '#', ['id' => 'js-import',]) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete selected'), '#', ['class' => 'js-button-delete js-selected disabled']) ?>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li>
                            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Products archive'), ['archive']) ?>
                        </li>
                    </ul>
                </div>
            </div>
            <?= Html::a(Yii::t('app', 'Add usage'), ['usage/create'], ['class' => 'btn']) ?>
            <?= Html::a(Yii::t('app', 'Add sale'), ['sale/create'], ['class' => 'btn']) ?>
            <?= Html::a(Yii::t('app', 'Add product'), ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>

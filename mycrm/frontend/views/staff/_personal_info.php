<?php
use yii\helpers\ArrayHelper;

/** @var $model \core\models\Staff */

$this->params['mainContentClass'] = 'settings';
?>

<div class="employee_show">
    <div class="avatar">
        <?= \yii\helpers\Html::img(\yii\helpers\Url::to(['image/image', 'id' => $model->image_id, 'size' => 208]),
            ['alt' => $model->name, 'class' => $model->color, '
                qtip-toggle' => ($model->image_id != Yii::$app->params['staffDefaultImageId'] ? 'tooltip' : ''),
             'id' => 'staff_avatar', 'width' => '208px', 'height' => '208px'])?>
        <div class="tooltip-content" style="display:none">
            <a href="/staff/delete-image/?id=<?= $model->id ?>" role="button" id="delete-staff-img">Удалить</a>
        </div>
    </div>
    <div class="employee_data container">
        <h2><?= "{$model->name} {$model->surname}" ?></h2>
        <?php if (!empty($model->phone)): ?>
        <div class="row row-bottom-margin">
            <div class="col-xs-1">
                <span class="icon sprite-employed_telephone"></span>
            </div>
            <div class="col-xs-10 no-padding">
                <?= \yii\helpers\Html::a($model->phone, "tel:".$model->getPlainPhone())?>
            </div>
        </div>
        <?php endif; ?>
        <div class="row row-bottom-margin">
            <div class="col-xs-1">
                <span class="icon sprite-employed_permissions"></span>
            </div>
            <div class="col-xs-10 no-padding">
                <?= implode(", ",
                    ArrayHelper::getColumn(
                        $model->companyPositions,
                        'name'
                    )
                ); ?>
            </div>
        </div>
        <?php if ( ! empty($model->divisionServices)): ?>
            <div class="row row-bottom-margin">
                <div class="col-xs-1">
                    <span class="icon sprite-employed_branches"></span>
                </div>
                <div class="col-xs-10 no-padding">
                    <?= implode(", ",
                        ArrayHelper::getColumn(
                            $model->divisionServices,
                            'service_name'
                        )
                    ); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($model->description)): ?>
            <div class="row row-bottom-margin">
                <div class="col-xs-1">
                    <span class="icon sprite-employed_description"></span>
                </div>
                <div class="col-xs-10 no-padding">
                    <?= $model->description ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($model->description_private)): ?>
            <div class="row row-bottom-margin">
                <div class="col-xs-1">
                    <span class="icon sprite-employed_description"></span>
                </div>
                <div class="col-xs-10 no-padding">
                    <?= $model->description_private ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

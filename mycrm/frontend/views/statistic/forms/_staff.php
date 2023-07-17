<?php

use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\ServiceCategory;
use core\models\warehouse\Product;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var \core\forms\customer\StatisticStaffForm $model */
?>

<?php $form = ActiveForm::begin([
    'action' => ['staff'],
    'method' => 'get',
]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'from', [
                'template' =>
                    '<div class="input-group"><span class="input-group-addon">'
                    . Yii::t('app',    'From date')
                    . '</span>{input}</div>{hint}',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ])->hint(sprintf('Выбран период длительностью %d суток', $model->difference)) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'to', [
                'template' =>
                    '<div class="input-group"><span class="input-group-addon">'
                    . Yii::t('app', 'To date')
                    . '</span>{input}</div>',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?php
            $divisions = Division::getOwnDivisionsNameList();
            if (sizeof($divisions) == 1) {
                $model->division_id = key($divisions);
            }
            echo $form->field($model, 'division_id')->dropDownList(
                $divisions,
                ['prompt' => Yii::t('app', 'All Divisions')]
            );
            ?>
        </div>
        <div class="col-md-4">
            <?php
            $data = $model->service_categories ? ServiceCategory::find()->select([
                ServiceCategory::tableName() . '.id',
                ServiceCategory::tableName() . '.name'
            ])
                ->filterByDivision($model->division_id)
                ->byId($model->service_categories)
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>

            <?= $form->field($model, 'service_categories')->widget(DepDrop::className(), [
                'type'           => DepDrop::TYPE_SELECT2,
                'data'           => $data,
                'pluginOptions'  => [
                    'depends'     => [Html::getInputId($model, 'division_id')],
                    'url'         => Url::to(['/service-category/list']),
                    'placeholder' => Yii::t('app', 'All Categories'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'pluginEvents'   => [
                    "depdrop:afterChange" => "function(event, id, value, count) { $(this).change(); }",
                ],
                'select2Options' => [
                    'size' => 'sm',
                ],
                'options'        => ['placeholder' => Yii::t('app', 'All Categories'), 'multiple' => true],
            ]) ?>
        </div>
        <div class="col-md-4">
            <?php
            $data = $model->service_id ? DivisionService::find()->select([
                DivisionService::tableName() . '.id',
                DivisionService::tableName() . '.service_name as name'
            ])
                ->division($model->division_id)
                ->byId($model->service_id)
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>

            <?= $form->field($model, 'service_id')->widget(DepDrop::className(), [
                'type'           => DepDrop::TYPE_SELECT2,
                'data'           => $data,
                'pluginOptions'  => [
                    'depends'     => [Html::getInputId($model, 'division_id')],
                    'url'         => Url::to(['/division/service/list']),
                    'placeholder' => Yii::t('app', 'All services'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'pluginEvents'   => [
                    "depdrop:change" => "function(event, id, value, count) { $(this).change(); }",
                ],
                'options'        => ['placeholder' => Yii::t('app', 'All services')],
                'select2Options' => [
                    'size'          => Select2::SMALL,
                    'pluginOptions' => ['allowClear' => true]
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?php
            $data = $model->product_categories ? \core\models\warehouse\Category::find()->select([
                \core\models\warehouse\Category::tableName() . '.id',
                \core\models\warehouse\Category::tableName() . '.name'
            ])
                ->joinWith('products', false)
                ->andWhere([
                    'division_id'                => $model->division_id,
                    '{{%warehouse_category}}.id' => $model->product_categories
                ])
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>
            <?= $form->field($model, 'product_categories')->widget(DepDrop::class, [
                'type'           => DepDrop::TYPE_SELECT2,
                'data'           => $data,
                'pluginOptions'  => [
                    'depends'     => [Html::getInputId($model, 'division_id')],
                    'url'         => Url::to(['/warehouse/category/list']),
                    'placeholder' => Yii::t('app', 'All Categories'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'pluginEvents'   => [
                    "depdrop:afterChange" => "function(event, id, value, count) { $(this).change(); }",
                ],
                'select2Options' => [
                    'size' => 'sm',
                ],
                'options'        => ['placeholder' => Yii::t('app', 'All Categories'), 'multiple' => true],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?php
            $data = $model->product_id ? Product::find()->select([
                Product::tableName() . '.id',
                Product::tableName() . '.name'
            ])
                ->division($model->division_id)
                ->byId($model->product_id)
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>
            <?= $form->field($model, 'product_id')->widget(DepDrop::class, [
                'type'           => DepDrop::TYPE_SELECT2,
                'data'           => $data,
                'pluginOptions'  => [
                    'depends'     => [Html::getInputId($model, 'division_id')],
                    'url'         => Url::to(['/warehouse/product/list']),
                    'placeholder' => Yii::t('app', 'All Products'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'pluginEvents'   => [
                    "depdrop:change" => "function(event, id, value, count) { $(this).change(); }",
                ],
                'options'        => ['placeholder' => Yii::t('app', 'All Products')],
                'select2Options' => [
                    'size'          => Select2::SMALL,
                    'pluginOptions' => ['allowClear' => true]
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary pull-right"><?= Yii::t('app', 'Search') ?></button>
        </div>
    </div>
<?php ActiveForm::end(); ?>
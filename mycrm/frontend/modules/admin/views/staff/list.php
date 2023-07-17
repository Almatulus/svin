<?php

use core\models\company\CompanyPosition;
use core\models\Staff;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Staff');
$this->params['breadcrumbs'][] = [
    'template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title
];
$this->params['bodyID'] = 'summary';
?>

    <div class="staff-index">

        <?php echo $this->render('_search', ['model' => $searchModel]) ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'striped'      => true,
            'condensed'    => true,
            'responsive'   => true,
            'columns'      => [
                ['class' => '\kartik\grid\CheckboxColumn'],
                [
                    'format'    => 'raw',
                    'attribute' => 'name',
                    'value'     => function ($data) {
                        return "{$data->name} {$data->surname}";
                    }
                ],
                'phone',
                [
                    'format'         => 'html',
                    'label'          => Yii::t('app', 'Company positions'),
                    'value'          => function(Staff $model) {
                        return implode(CompanyPosition::STRING_DELIMITER,
                            ArrayHelper::getColumn(
                                $model->companyPositions,
                                function (CompanyPosition $companyPosition) {
                                    return $companyPosition->name;
                                }
                            )
                        );
                    },
                    'contentOptions' => ['class' => 'role']
                ],
                [
                    'format'         => 'html',
                    'label'          => Yii::t('app', 'Divisions'),
                    'value'          => function (Staff $model) {
                        return implode('<br>', array_map(function ($division) {
                            return $division->name;
                        }, $model->divisions));
                    },
                    'contentOptions' => ['class' => 'role']
                ],
            ],
        ]); ?>

    </div>


<?php

$js = <<<JS
$('.js-staff-send-sms').click(bulkSms);

function bulkSms(e) {
    e.preventDefault();
    bootbox.prompt({
        title: "Введите сообщение",
        inputType: 'textarea',
        callback: function (result) {
            var selected = [];
            $.each($('.grid-view input[type=checkbox]:checked'), function(ind, el) {
                selected.push(el.getAttribute('value'));
            });
            if (result && selected.length >= 1) {
                $.post("sms", {"selected": selected, "message": result})
                    .done(function (response) {
                        if (response.errors) {
                            alertMessage("Произошла ошибка при валидации данных");
                        } else {
                            alertMessage(response.message);
                        }
                    }).fail(function () {
                        alertMessage("Произошла ошибка");
                    });
            }
        }
    });
}
JS;
$this->registerJs($js);

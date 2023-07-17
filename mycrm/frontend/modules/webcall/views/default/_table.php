<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $results array */
/* @var $totalCount integer */

$dataProvider = new \yii\data\ArrayDataProvider([
    'models'     => $results,
    'pagination' => new \yii\data\Pagination([
        'totalCount'      => $totalCount,
        'pageSizeParam'   => 'WebCallForm[items_on_page]',
        'pageParam'       => 'WebCallForm[page]',
        'defaultPageSize' => 100,
        'pageSizeLimit'   => [1, 100]
    ])
]);
$dataProvider->setTotalCount($totalCount);
?>
<div class="row">
    <div class="col-sm-12">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $dataProvider,
            'summary'      => \core\helpers\HtmlHelper::getSummary($dataProvider->pagination->pageSizeParam, 100),
            'columns'      => [
                [
                    'attribute' => 'direction',
                    'label'     => Yii::t('app', 'direction'),
                    'value'     => function ($object) {
                        $data = [
                            0 => Yii::t('app', 'Incoming call'),
                            1 => Yii::t('app', 'Outgoing call')
                        ];
                        return $data[$object->direction] ?? null;
                    }
                ],
                [
                    'attribute' => 'client_number',
                    'label'     => 'Клиент',
                    'value'     => function ($object) {
                        $phone = $object->client_number;

                        if (!empty($phone) && preg_match("/^\+(\d)(\d{3})(\d{3})(\d{2})(\d{2})$/", $phone, $matches)) {
                            $phone = "+" . $matches[1] . " " . $matches[2] . " " . $matches[3] . " " . $matches[4] . " " . $matches[5];
                            $companyCustomer = \core\models\customer\CompanyCustomer::find()->company()->joinWith('customer')
                                ->andWhere(['phone' => $phone])->one();
                            return $companyCustomer ? $companyCustomer->customer->name : $object->client_number;
                        }

                        return $phone;
                    }
                ],
                'start_time:datetime:' . Yii::t('app', 'start_time'),
                'duration:text:' . Yii::t('app', 'duration'),
                'answer_time:datetime:' . Yii::t('app', 'answer_time'),
                [
                    'attribute' => 'recording',
                    'format'    => 'raw',
                    'label'     => Yii::t('app', 'recording'),
                    'value'     => function ($object) {
                        return Html::tag('audio', Html::tag('source', '', [
                            'src'  => $object->recording,
                            'type' => 'audio/mpeg'
                        ]), ['controls' => 0]);
                    }
                ],
                'answered:boolean:' . Yii::t('app', 'answered'),
            ]
        ])
        ?>
    </div>
</div>


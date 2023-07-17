<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \core\models\Staff
 */

$this->title                   = Yii::t('app', 'Staff');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = "{$model->name} {$model->surname}";
?>

<div class="staff-update">
    <div class="column_row buttons-row">
        <div class="right-buttons">
            <?php if (YII_ENV_PROD && isset($model->user) && !$model->user->google_refresh_token) {
                $url = \Yii::$app->googleApiClient->getAuthUrl(json_encode(['staff_id' => $model->id]));
            ?>
                <a href="<?= $url ?>"  class="btn btn-default">Подключить Google Calendar</a>
            <?php } else if (YII_ENV_PROD && isset($model->user) && $model->user->google_refresh_token) {
                echo Html::a("Отключить Google Calendar", ['disable-calendar', 'id' => $model->id], ['class' => 'btn btn-default']);
            } ?>
            <?= Html::a(Yii::t('app', 'Schedule'), ['schedule', 'id' => $model->id], [
                'class' => 'btn btn-default',
            ]) ?>
            <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-default">
                Редактировать
            </a>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-default',
                'onclick' => 'return false',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this staff?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>
    <?= Tabs::widget([
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<span class="icon sprite-employed_summation"></span> Личная информация',
                'content' => $this->render('_personal_info', ['model' => $model]),
                'active' => true
            ],
            [
                'label' => '<span class="icon sprite-employed_history"></span> История записей',
                'content' => $this->render('_history', ['model' => $model, 'dataProvider' => $dataProvider,]),
                'options' => ['class' => 'tab']
            ],
        ],
        'options' => ['class' => 'tab_list']
    ]);
    ?>
</div>
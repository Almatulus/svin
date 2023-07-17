<?php


/* @var $this yii\web\View */
/* @var $model core\models\Company */

$this->title                   = Yii::t('app','Statistic').' - '.Yii::t('app','notifications');

$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>', 'label' => Yii::t('app', 'Statistic'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Notifications');
?>
<div class="statistic">
    <div class="stat-notifications">

        <div class="row">
            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается лимит email уведомлений
                        на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Лимит email уведомлений</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->email_limit, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается лимит push уведомлений
                        на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Лимит push уведомлений</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->push_limit, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается лимит push уведомлений
                        на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Лимит sms уведомлений</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->sms_limit, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row text-center">

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается количество отправленных email уведомлений на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Email уведомления</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->email_count, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается количество отправленных push уведомлений на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Push уведомления</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->push_count, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip" title='Отображается количество отправленных sms уведомлений на компанию'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green bold uppercase">Sms уведомления</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= number_format($model->sms_count, 0, '.', ' ') ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

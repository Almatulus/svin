<?php

use core\helpers\MenuList;
use yii\base\Widget;
use yii\widgets\ActiveForm;

/* @var $form Widget */
?>
<div class="sidebar hidden-print" id="sidebar">
    <?php if (isset($this->params['renderForm'])): ?>
        <?php $form = ActiveForm::begin($this->params['formOptions']); ?>
    <?php endif; ?>

    <?php
    $modules = MenuList::modules();
    $moduleItems = MenuList::moduleItems(Yii::$app->request->url, $this->params);
    $moduleOptions = MenuList::moduleOptions();

    $menuItems = [];
    foreach ($modules as $key => $moduleKey) {
        $permissions = MenuList::permissions()[$moduleKey];

        $label = MenuList::moduleLabels()[$moduleKey];
        $lOptions = $moduleOptions[$moduleKey];
        $items = $moduleItems[$moduleKey];

        $url = MenuList::moduleUrls()[$moduleKey];

        $visible = false;

        if (is_bool($permissions) || $moduleKey == MenuList::SETTINGS) {
            $visible = $permissions;
        } else {
            foreach ($permissions as $permission) {
                if (Yii::$app->user->can($permission)) {
                    $visible = true;
                    break;
                }
            }
        }

        if ($visible) {

            foreach ($items as $item) {
                if (isset($item['visible']) && $item['visible']) {
                    $url = $item['url'];
                    break;
                }
            }

            array_push($menuItems, [
                'id'      => $moduleKey,
                'label'   => '<div class="ico"></div><span>' . $label . '</span>',
                'url'     => $url,
                'options' => $lOptions,
                'items'   => $items
            ]);
        }
    }

    if (Yii::$app->user->can('administrator')) {
        array_push($menuItems, [
            'id' => 'settings',
            'label' => '<div class="ico"></div><span>' . Yii::t('app', 'Administrator Panel') . '</span>',
            'url' => ['/admin/user/index'],
            'options' => ['class' => 'settings'],
            'items' => [
                [
                    'label' => Yii::t('app', 'Document Suggestions'),
                    'url' => ['/admin/document-suggestion/index'],
                    'active' => strpos(Yii::$app->request->url, '/admin/document-suggestion/') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Orders'),
                    'url' => ['/admin/order/index'],
                    'active' => strpos(Yii::$app->request->url, '/admin/order/') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label'  => Yii::t('app', 'Time Schedule'),
                    'url'    => ['/admin/staff/index'],
                    'active' => strpos(Yii::$app->request->url, '/admin/staff/index') !== false,
                    'icon'   => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Staff Services'),
                    'url' => ['/admin/staff/services'],
                    'active' => strpos(Yii::$app->request->url, '/admin/staff/services') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Active Companies'),
                    'url' => ['/admin/company/active'],
                    'active' => strpos(Yii::$app->request->url, '/admin/company/active') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Statistics'),
                    'url' => ['/admin/statistic/index'],
                    'active' => strpos(Yii::$app->request->url, '/admin/statistic/index') !== false,
                    'icon' => 'fa fa-chart-pie',
                ],
                [
                    'label' => Yii::t('app', 'Staff'),
                    'url' => ['/admin/staff/list'],
                    'active' => strpos(Yii::$app->request->url, '/admin/staff/list') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Services'),
                    'url' => ['/service-category/index'],
                    'active' =>
                        strpos(Yii::$app->request->url, '/service-category/') !== false ||
                        substr(Yii::$app->request->url, 0, strlen('/service/')) === '/service/',
                    'icon' => 'fa fa-users',
                ],
                [
                    'label' => Yii::t('app', 'Users'),
                    'url' => ['/admin/user/index'],
                    'active' => strpos(Yii::$app->request->url, '/admin/user/') !== false,
                    'icon' => 'fa fa-users',
                ],
                [
                    'label'  => Yii::t('app', 'Tariffs'),
                    'url'    => ['/admin/tariff/index'],
                    'icon'   => 'fa fa-money-bill-alt',
                    'active' => strpos(Yii::$app->request->url, '/admin/tariff') !== false,
                ],
                [
                    'label'  => Yii::t('app', 'Companies'),
                    'url'    => ['/admin/company/index'],
                    'icon'   => 'fa fa-users',
                    'active' => strpos(Yii::$app->request->url, '/admin/company') !== false ||
                        strpos(Yii::$app->request->url, '/admin/task') !== false,
                ],
                [
                    'label' => Yii::t('app', 'Divisions'),
                    'url' => ['/admin/division/index'],
                    'icon' => 'fa fa-users',
                    'active' => strpos(Yii::$app->request->url, '/admin/division') !== false,
                ],
                [
                    'label' => Yii::t('app', 'Comments'),
                    'url' => ['/admin/comment-category/index'],
                    'icon' => 'fa fa-image',
                    'active' => strpos(Yii::$app->request->url, '/comment-category/') !== false ||
                        strpos(Yii::$app->request->url, '/comment/') !== false
                ],
                [
                    'label' => Yii::t('app', 'FAQ'),
                    'url' => ['/faq/index'],
                    'icon' => 'fa fa-question-circle',
                    'active' => strpos(Yii::$app->request->url, '/faq/') !== false
                ],
                [
                    'label'  => Yii::t('app', 'Forms'),
                    'url'    => ['/document/form/index'],
                    'icon'   => 'fa fa-image',
                    'active' => strpos(Yii::$app->request->url, '/document/form/') !== false
                ],
                [
                    'label'  => Yii::t('app', 'Form Groups'),
                    'url'    => ['/document/group/index'],
                    'icon'   => 'fa fa-image',
                    'active' => strpos(Yii::$app->request->url, '/document/group/') !== false
                ],
                [
                    'label'  => Yii::t('app', 'Positions'),
                    'url'    => ['/admin/position'],
                    'icon'   => 'fa fa-users',
                    'active' => strpos(Yii::$app->request->url, '/admin/position') !== false
                ],
                [
                    'label'  => Yii::t('app', 'News Logs'),
                    'url'    => ['/admin/news-log/index'],
                    'icon'   => 'fa fa-newspaper',
                    'active' => strpos(Yii::$app->request->url, '/admin/news-log/') !== false
                ],
                [
                    'label'  => Yii::t('app', 'User Logs'),
                    'url'    => ['/admin/user-log/index'],
                    'icon'   => 'fa fa-file',
                    'active' => strpos(Yii::$app->request->url, '/admin/user-log/') !== false
                ],
            ],
        ]);
    }
    ?>

    <?= common\components\Menu::widget([
            'encodeLabels' => false,
            'options' => ['class' => 'nav'],
            'view' => (isset($this->params['sideNavView'])) ? $this->params['sideNavView'] : 'list',
            'items' => $menuItems,
            'renderForm' => isset($this->params['renderForm']) ? $this->params['renderForm'] : false,
            'formView' => isset($this->params['formViewUrl']) ? $this->render($this->params['formViewUrl'], ['form' => $form, 'model' => $this->params['formModel']]) : null,
    ]); ?>

    <?php if (isset($this->params['renderForm'])): ?>
        <?php $form->end(); ?>
    <?php endif; ?>
</div>

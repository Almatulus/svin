<?php

use core\models\company\Company;
use core\models\order\Order;

/* @var $this yii\web\View */
/* @var $companies Company[] */

$this->title = Yii::t('app', 'Active Companies');

$this->params['breadcrumbs']['label'] = $this->title;

$companies  = Company::find()
    ->innerJoinWith(['divisions.staffs', 'tariff'], [false, true])
                     ->orderBy('{{%companies}}.id')
                     ->where(['NOT IN', '{{%companies}}.id', [181, 56, 30]])
    ->indexBy('id')
                     ->all();
$count      = 0;
$categories = [];
$income     = 0;

$week_ago = date('Y-m-d 00:00:00', time() - 7 * 24 * 60 * 60);

$subQuery = Order::find()
    ->joinWith('division', false)
    ->select(['MAX(created_time) as created_time', 'company_id'])
    ->where([
        '>=',
        '{{%orders}}.created_time',
        $week_ago
    ])
    ->andWhere(['NOT IN', 'company_id', [181, 56, 30]])
    ->groupBy('company_id');

$lastOrders = Order::find()
    ->select(['ord.created_time', 'datetime', 'ord.company_id'])
    ->joinWith('division', false)
    ->innerJoin(['ord' => $subQuery],
        'ord.created_time = {{%orders}}.created_time AND ord.company_id = {{%divisions}}.company_id')
    ->indexBy('company_id')
    ->orderBy('ord.created_time DESC')
    ->asArray()
    ->all();
?>
<div class="company-index">

    <table class="kv-grid-table table table-bordered table-striped table-condensed kv-table-wrap">
        <tr>
            <th>#</th>
            <th><?= Yii::t('app', 'Company ID') ?></th>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th><?= Yii::t('app', 'Payment Amount') ?></th>
            <th><?= Yii::t('app', 'Category') ?></th>
            <th><?= Yii::t('app', 'Created Time') ?></th>
            <th><?= Yii::t('app', 'Datetime') ?></th>
        </tr>
        <?php foreach ($lastOrders as $company_id => $lastOrder): ?>
            <?php
            $company = $companies[$company_id] ?? null;
            if (!$company) {
                continue;
            }
            $income    += $company->tariff->price;
            ?>

            <tr>
                <td><?= ++$count ?></td>
                <td><?= $company->id ?></td>
                <td><?= $company->name ?></td>
                <td align="right"><?= Yii::$app->formatter->asDecimal($company->tariff->price) ?></td>
                <td>
                    <?php
                    $category_name = $company->category_id
                        ? $company->category->name : null;
                    $categories[$category_name]
                                   = isset($categories[$category_name])
                        ? $categories[$category_name] + 1 : 0;
                    ?>
                    <?= $category_name ?>
                </td>
                <td>
                    <?= Yii::$app->formatter->asDatetime($lastOrder['created_time']) ?>
                </td>
                <td>
                    <?= Yii::$app->formatter->asDatetime($lastOrder['datetime']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div>
        <strong>Итого:</strong> <?= $count ?><br>
        <?php foreach ($categories as $category_name => $category_count): ?>
            <strong><?= $category_name ?>:</strong> <?= $category_count ?><br>
        <?php endforeach; ?>
        <br>
        <strong>Ежемесячно: </strong><?= Yii::$app->formatter->asDecimal($income) ?>
    </div>

</div>

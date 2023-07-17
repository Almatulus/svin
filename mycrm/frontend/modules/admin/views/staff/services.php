<?php
use core\models\Staff;
use johnitvn\ajaxcrud\CrudAsset;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $staffs \core\models\Staff[] */

$this->title                   = 'Сотрудники без услуг';
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
$this->params['bodyID']        = 'summary';
CrudAsset::register($this);
?>
<div class="order-index">

    <table class="kv-grid-table table table-bordered table-striped table-condensed kv-table-wrap">
        <tr>
            <th>#</th>
            <th><?= Yii::t('app', 'Name')?></th>
            <th><?= Yii::t('app', 'Company')?></th>
        </tr>
        <?php foreach ($staffs as $index => $staff_item): ?>
            <?php if (in_array($staff_item->division->company_id, [30, 1, 4, 5])) { continue; }?>
            <tr>
                <td><?= $index+1 ?></td>
                <td><?= $staff_item->getFullName() ?></td>
                <td><?= $staff_item->division->company->name ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>
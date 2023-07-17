<?php
/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerRequest */
?>
    <tr>
        <td><?= $model->created_time ?></td>
        <td><?= $model->type ?></td>
        <td><?= $model->status ?></td>
        <td><?= 'updated' ?></td>
    </tr>
    <tr>
        <td colspan="4">
            <i><?= $model->code ?></i>
        </td>
    </tr>
<?php



/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCash */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['template' => '<li><i class="fa fa-credit-card"></i> {link}</li>', 'label' => Yii::t('app','Cashes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-cash-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

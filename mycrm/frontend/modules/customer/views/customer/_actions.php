<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$class = 'btn btn-sm btn-flat btn-default btn-block';

?>

<div class="customer-actions">
	<div class="dropdown inline_block">
		<button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
			Действия <b class="caret"></b>
		</button>
		<ul class="dropdown-menu">
			<li>
				<?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('app', 'Send SMS selected'), '#', ['class' => 'js-button-request js-selected disabled']) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('app', 'Send SMS fetched'), '#', ['class' => 'js-button-request js-fetched']) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('app', 'Send SMS all'), '#', ['class' => 'js-button-request js-all']) ?>
			</li>
			<li role="separator" class="divider"></li>
            <li>
                <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete selected'), '#', ['class' => 'js-button-delete js-selected disabled']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete fetched'), '#', ['class' => 'js-button-delete js-fetched']) ?>
            </li>
            <li>
				<?= Html::a('<i class="fa fa-users"></i> ' . Yii::t('app', 'Add selected to category'), '#', ['class' => 'js-button-category js-selected disabled']) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-users"></i> ' . Yii::t('app', 'Add fetched to category'), '#', ['class' => 'js-button-category js-fetched']) ?>
			</li>
            <li>
                <?= Html::a('<i class="fa fa-copy"></i> ' . Yii::t('app', 'Merge selected'), '#',
                    ['class' => 'js-merge-selected js-selected disabled']) ?>
            </li>
            <li role="separator" class="divider"></li>
			<li>
				<?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export fetched to Excel'), '#', ['id' => 'js-export-fetched',]) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export all to Excel'), '#', ['id' => 'js-export-all']) ?>
			</li>
			<li role="separator" class="divider"></li>
			<li>
				<?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Download template'), '#', ['id' => 'js-download-template']) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Import from Excel'), '#', ['id' => 'js-import',]) ?>
			</li>
			<li>
				<?= Html::a('<i class="fa fa-cloud"></i> ' . Yii::t('app', 'Last Customers Import'), ['temp/index']) ?>
			</li>
            <li role="separator" class="divider"></li>
            <li>
                <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Customers archive'), ['archive']) ?>
            </li>
		</ul>
	</div>
    <?php if (Yii::$app->user->can("companyCustomerCreate") &&
              Yii::$app->user->identity->canSeeCustomerPhones()): ?>
        <?= Html::a('<i class="icon sprite-add_customer"></i>' . 'Создать клиента', ['create'], ['class' => 'btn btn-primary', 'data-push' => '0']) ?>
    <?php endif; ?>
</div>
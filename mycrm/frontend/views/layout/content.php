<?php
use yii\widgets\Breadcrumbs;

$mainContentClass = isset($this->params['mainContentClass']) ? $this->params['mainContentClass'] : '';
$innerClass       = isset($this->params['innerClass']) ? $this->params['innerClass'] : '';
?>

<div class="main-content <?= $mainContentClass ?>" id="main-content" role="main">
    <div class="main-content-cover"></div>
	<?php $activeItemTemplate = "<li class=\"active\">{link}</li>\n";
	if (isset($this->params['breadcrumbs']) && sizeof($this->params['breadcrumbs']) > 1) {
		$activeItemTemplate = "<li class=\"active\"> / {link}</li>\n";
	}
	?>
	<?php if (!(isset($this->params['bodyID']) && $this->params['bodyID'] == 'calendar')) { ?>
		<div class="breadcrumbs">
			<?= Breadcrumbs::widget([
				'activeItemTemplate' => $activeItemTemplate,
				'itemTemplate' => '<li> / {link}</li>',
				'encodeLabels' => false,
				'homeLink' => false,
				'options' => ['class' => ''],
				'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
			]) ?>
		</div>
	<?php } ?>
	<div class="inner <?= $innerClass ?>">
		<?= \kartik\alert\AlertBlock::widget([
			'type' => \kartik\alert\AlertBlock::TYPE_ALERT,
			'useSessionFlash' => true,
            'delay' => false
		]) ?>
        <?= $content ?>
    </div>
</div>
<?php
/* @var $this yii\web\View */

$this->title                   = Yii::t('app', 'FAQ');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_help'></div><h1>{$this->title}</h1>";
$this->params['bodyClass']     = 'no_sidenav';

$parser = new \cebe\markdown\GithubMarkdown();
?>

<div class="panel-group" id="faq" role="tablist" aria-multiselectable="true">
  <?php foreach ($models as $key => $model) { ?>
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?= $key ?>" aria-expanded="true" aria-controls="collapseOne">
              <?= $model->question?>
          </a>
        </h4>
      </div>
      <div id="collapse-<?= $key ?>" class="panel-collapse collapse <?= $key == 0 ? "in" : "" ?>" role="tabpanel" aria-labelledby="headingOne">
        <div class="panel-body">
          <?= $parser->parse($model->answer);?>
        </div>
      </div>
  </div>
  <?php } ?>
</div>

<?php
/* @var View $this */
/* @var \core\models\order\Order $model */
/* @var MedCardCommentCategory[] $comment_categories */

/* @var MedCardCompanyComment[] $company_comments */

use core\models\medCard\MedCardCommentCategory;
use core\models\medCard\MedCardCompanyComment;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/* @var \core\models\user\User $user */
$user    = Yii::$app->user->identity;
$user_token = $user->getValidAccessToken();
$company = $user->company;

$comment_categories = MedCardCommentCategory::find()
    ->serviceCategory($company->category_id)
    ->orderBy('order')
    ->all();

$enabled_categories = ArrayHelper::getColumn($comment_categories, 'id');
if (($staff = $user->staff) !== null) {
    if ($staff->companyPositions) { // This check is redundant, I guess
        $temp_enabled_categories = [];
        foreach ($staff->companyPositions as $companyPosition) {
            $temp_enabled_categories = array_merge($temp_enabled_categories, ArrayHelper::getColumn($companyPosition->medCardCommentCategories, 'id'));
        }
        $temp_enabled_categories = array_unique($temp_enabled_categories);
        if ( ! empty($temp_enabled_categories)) {
            $enabled_categories = $temp_enabled_categories;
        }
    }
}

$company_comments    = MedCardCompanyComment::find()
    ->where(['company_id' => $company->id])
    ->all();
?>
<div class="panel-group comments-form" id="comments-accordion" role="tablist"
     aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><?= Yii::t('app', 'Diagnosis') ?></h4>
        </div>
        <div class="panel-collapse">
            <div class="panel-body">
                <?php
                    $company = Yii::$app->user->identity->company;
                    $filter = $company->isStomCategory() ? 'service_category_id=' . $company->category_id : '';
                ?>
                <?= \kartik\select2\Select2::widget([
                    'id'            => 'js-diagnoses-list',
                    'name'          => 'MedCard[diagnosis_id]',
                    'size'          => 'sm',
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language'   => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax'       => [
                            'url'            => Url::to(Yii::$app->params['api_host'] . '/v2/diagnosis?'.$filter),
                            'dataType'       => 'json',
                            'data'           => new JsExpression('function(params) { return {q:params.term}; }'),
                            'processResults' => new JsExpression('function (data, params) {
                             var results = $.map(data, function(obj){
                                obj.text = obj.text || "(" + obj.code + ") " + obj.name;
                                return obj;
                             });
                             return {results: results};
                         }'),
                        ],
                    ],
                    'pluginEvents'  => [
                        'change' => new JsExpression(
                            "function () { refreshComments($(this).val(), '{$user_token}') }"
                        ),
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <?php foreach ($comment_categories as $category): ?>
        <?php
        if ( ! in_array($category->id, $enabled_categories)) {
            continue;
        }
        ?>
        <div class="panel panel-default">
            <div class="panel-heading" role="tab"
                 id="comment-heading-<?= $category->id ?>">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse"
                       data-parent="#comments-accordion"
                       href="#comment-collapse-<?= $category->id ?>"
                       aria-expanded="true"
                       aria-controls="comment-collapse-<?= $category->id ?>">
                        <?= $category->name ?>
                    </a>
                </h4>
            </div>
            <div id="comment-collapse-<?= $category->id ?>"
                 class="panel-collapse collapse" role="tabpanel"
                 aria-labelledby="comment-heading-<?= $category->id ?>">
                <div class="panel-body">
                    <?php echo Html::textarea("MedCard[comments][{$category->id}]",
                        null, [
                            'id'            => 'js-order_comment_' . $category->id,
                            'class'         => 'js-order_comment_item order_comment_item comments_autocomplete',
                            'placeholder'   => 'начните печатать',
                            'style'         => "min-height: 100px",
                            'data-category' => $category->id
                        ]) ?>
                </div>
                <div class="panel-footer">
                    <?= Html::a(
                        Yii::t('app', 'Start recognition'),
                        '',
                        [
                            'class'       => 'js-voice-recognition btn btn-default',
                            'data-target' => 'js-order_comment_' . $category->id,
                            'x-webkit-speech' => ''
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        <?php
        $script
            = <<<JS
$(function(){
    setupCompleteInput({$category->id}, '{$user_token}');
});
JS;
        $this->registerJs($script);
        ?>
    <?php endforeach; ?>
</div>

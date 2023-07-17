<?php

use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCommentCategory;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\MedCardCommentCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $categories core\models\medCard\MedCardCommentCategory[] */

$this->title = Yii::t('app', 'Comment Template Category');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-template-category-index">

    <?php echo $this->render('_search', [
        'model' => $searchModel,
        'categories' => ArrayHelper::map($categories, 'id', 'name')
    ]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t(
            'app', 'Create Comment'),
            ['comment/create'], ['class' => 'btn btn-default']
        ) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            [
                'attribute' => 'parent_id',
                'value' => function (MedCardCommentCategory $model) {
                    return $model->parent ? $model->parent->name : null;
                }
            ],
            [
                'attribute' => 'service_category_id',
                'value' => function (MedCardCommentCategory $model) {
                    return $model->serviceCategory->name;
                }
            ],
            [
                'label' => Yii::t('app', 'Comments'),
                'format' => 'html',
                'value' => function (MedCardCommentCategory $model) {
                    return implode('<hr>', array_map(function (MedCardComment $model) {
                        return Html::a(
                            Yii::t('app', $model->comment),
                            ['comment/update', 'id' => $model->id]
                        );
                    }, $model->templates));
                }
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}'],
        ],
    ]); ?>
</div>

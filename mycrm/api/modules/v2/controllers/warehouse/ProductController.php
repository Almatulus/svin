<?php

namespace api\modules\v2\controllers\warehouse;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\warehouse\WarehouseProductSearch;
use core\models\warehouse\Category;
use core\models\warehouse\Product;
use core\models\warehouse\query\ProductQuery;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class ProductController extends BaseController
{
    public $modelClass = 'core\models\warehouse\Product';

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'categories',
                        'view',
                        'options',
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new WarehouseProductSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionCategories()
    {
        $categories = Category::find()->company()
            ->innerJoinWith([
                'products' => function (ProductQuery $query) {
                    return $query->active()->permitted();
                }
            ])
            ->orderBy('name ASC')
            ->asArray()
            ->all();

        foreach ($categories as $key => $category) {
            $categories[$key]['products'] = Product::find()->filterByCategory($category['id'])->active()->permitted()->all();
        }

        $productsWithoutCategory = Product::find()->company()->active()->permitted()->withoutCategory()/*->asArray()*/
        ->all();

        if ($productsWithoutCategory) {
            $categories[] = [
                'id'         => null,
                'name'       => 'Без категории',
                'company_id' => \Yii::$app->user->identity->company_id,
                'parent_id'  => null,
                'products'   => $productsWithoutCategory
            ];
        }

        return $categories;
    }

    /**
     * @param string  $action
     * @param Product $model
     * @param array   $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view'])) {
            $permitted = in_array(
                $model->division_id,
                \Yii::$app->user->identity->permittedDivisions
            );
            if ( ! $permitted) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }
}

<?php

namespace api\modules\v2\controllers\warehouse;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\warehouse\CategorySearch;
use core\models\warehouse\Category;
use core\repositories\exceptions\NotFoundException;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class CategoryController extends BaseController
{
    public $modelClass = 'core\models\warehouse\Category';

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
                        'create',
                        'update',
                        'delete',
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
        unset($actions['create'], $actions['update']);
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
        $searchModel = new CategorySearch();
        $searchModel->setCompanyId(\Yii::$app->user->identity->company_id);

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @return Category
     */
    public function actionCreate()
    {
        $model = new Category();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->load(Yii::$app->request->bodyParams, "");
        $model->save();
        return $model;
    }

    /**
     * @param $id
     * @return Category
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $model->load(Yii::$app->request->bodyParams, "");
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return Category
     */
    private function findModel($id)
    {
        if (($model = Category::findOne($id))) {
            return $model;
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * @param string $action
     * @param Category $model
     * @param array $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update', 'delete'])) {
            if ($model->company_id != \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

}
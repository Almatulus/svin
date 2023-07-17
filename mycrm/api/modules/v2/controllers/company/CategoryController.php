<?php

namespace api\modules\v2\controllers\company;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\company\CategorySearch;
use core\models\ServiceCategory;
use core\repositories\exceptions\NotFoundException;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class CategoryController extends BaseController
{
    public $modelClass = 'core\models\ServiceCategory';

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
                        'update',
                        'delete',
                        'view',
                        'options',
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
                [

                    'actions' => ['create'],
                    'allow'   => true,
                    'roles'   => ['serviceCategoryCreate'],
                ]
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
        $searchModel = new CategorySearch();
        $company = Yii::$app->user->identity->company;
        $searchModel->setCompanyId($company->id);
        $searchModel->setDivisionCategoryIds($company->getDivisions()->enabled()->select('category_id')->column());

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @return ServiceCategory
     */
    public function actionCreate()
    {
        $model = new ServiceCategory();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->type = ServiceCategory::TYPE_CATEGORY_DYNAMIC;
        $model->load(Yii::$app->request->bodyParams, "");
        $model->save();
        return $model;
    }

    /**
     * @param $id
     * @return ServiceCategory
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
     * @return ServiceCategory
     */
    private function findModel($id)
    {
        if (($model = ServiceCategory::find()->enabled()->byId($id)->one())) {
            return $model;
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * @param string $action
     * @param ServiceCategory $model
     * @param array $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update', 'delete'])) {
            $cannotBeEdited = in_array($action, ['update', 'delete']) ? $model->isStatic() : false;
            if ($model->company_id != \Yii::$app->user->identity->company_id || $cannotBeEdited) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @param $id
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $model->disable();

        Yii::$app->response->setStatusCode(204);
    }

}
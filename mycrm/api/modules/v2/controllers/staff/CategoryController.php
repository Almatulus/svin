<?php

namespace api\modules\v2\controllers\staff;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\staff\CategorySearch;
use core\models\Staff;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
                    'actions' => ['index', 'options'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * @param $staff_id
     * @return array
     */
    public function actionIndex($staff_id)
    {
        $model = $this->findModel($staff_id);

        $this->checkAccess($this->action->id, $model);

        $searchModel = new CategorySearch($staff_id);

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param int $id
     * @return Staff
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Staff::find()->enabled()->byId($id)->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param string $action
     * @param Staff $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        $permittedDivisionIds = \Yii::$app->user->identity->getPermittedDivisions();
        $staffDivisionIds = $model->getDivisions()->enabled()->select('id')->column();

        if (empty(array_intersect($staffDivisionIds, $permittedDivisionIds))) {
            throw new ForbiddenHttpException('You are not allowed to act on this object');
        }
    }
}
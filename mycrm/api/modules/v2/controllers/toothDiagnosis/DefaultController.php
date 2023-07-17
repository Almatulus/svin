<?php

namespace api\modules\v2\controllers\toothDiagnosis;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\medCard\MedCardToothDiagnosisSearch;
use core\models\medCard\MedCardToothDiagnosis;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\medCard\MedCardToothDiagnosis';

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
                        'view',
                        'create',
                        'update',
                        'delete',
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
        $searchModel = new MedCardToothDiagnosisSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string                $action
     * @param MedCardToothDiagnosis $model
     * @param array                 $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update', 'delete'])) {
            if ($model->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @return MedCardToothDiagnosis
     */
    public function actionCreate()
    {
        $model = new MedCardToothDiagnosis();
        $model->company_id = Yii::$app->user->identity->company_id;;

        $model->load(Yii::$app->request->bodyParams, "");
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return MedCardToothDiagnosis
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
     * @throws ServerErrorHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        if (!(empty($model->medCardTeeth) && $model->delete())) {
            throw new ServerErrorHttpException(Yii::t('app', 'Delete Error'));
        }

        Yii::$app->response->setStatusCode(204);
    }


    /**
     * @param $id
     * @return MedCardToothDiagnosis
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        if (($model = MedCardToothDiagnosis::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

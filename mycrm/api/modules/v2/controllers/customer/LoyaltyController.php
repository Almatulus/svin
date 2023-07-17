<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\controllers\BaseController;;
use api\modules\v2\search\customer\CustomerLoyaltySearch;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerLoyalty;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class LoyaltyController extends BaseController
{
    public $modelClass = 'core\models\customer\CustomerLoyalty';

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
                        'options',
                        'create',
                        'update'
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
        $searchModel = new CustomerLoyaltySearch();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param string          $action
     * @param CustomerCategory $model
     * @param array           $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update'])) {
            if ($model->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @return CustomerLoyalty
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new CustomerLoyalty();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->company_id = Yii::$app->user->identity->company_id;

        if ( ! $model->defineScenario()) {
            throw new BadRequestHttpException('Invalid mode.');
        }

        $model->load(Yii::$app->request->bodyParams, '');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $model->save();
        $model->refresh();

        return $model;
    }

    /**
     * @param $id
     * @return CustomerLoyalty
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id){
        $model = CustomerLoyalty::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Model not exists');
        }

        $model->load(Yii::$app->request->bodyParams, '');
        $model->company_id = Yii::$app->user->identity->company_id;

        if ( ! $model->defineScenario()) {
            throw new BadRequestHttpException('Invalid mode.');
        }

        $model->load(Yii::$app->request->bodyParams, '');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $model->save();

        return $model;
    }
}

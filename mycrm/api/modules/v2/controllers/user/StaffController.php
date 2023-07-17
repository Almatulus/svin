<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\user\StaffSearch;
use core\forms\staff\ServicesForm;
use core\forms\staff\StaffCreateForm;
use core\forms\staff\StaffUpdateForm;
use core\models\Staff;
use core\services\staff\StaffModelService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class StaffController extends BaseController
{
    public $modelClass = 'core\models\Staff';

    /**
     * @var StaffModelService
     */
    private $staffs;

    /**
     * StaffController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param StaffModelService $staffs
     * @param array $config
     */
    public function __construct($id, $module, StaffModelService $staffs, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->staffs = $staffs;
    }

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
                        'create',
                        'view',
                        'fire',
                        'add-services',
                        'delete-services',
                        'options'
                    ],
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
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider'
        ];

        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function prepareDataProvider(): ActiveDataProvider
    {
        $searchModel = new StaffSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string $action
     * @param Staff $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update', 'fire', 'add-services', 'delete-services'])) {
            $permittedDivisionIds = \Yii::$app->user->identity->getPermittedDivisions();
            $staffDivisionIds = $model->getDivisions()->enabled()->select('id')->column();

            if (empty(array_intersect($staffDivisionIds, $permittedDivisionIds))) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @param integer $id
     *
     * @return Staff|StaffUpdateForm
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new StaffUpdateForm($model);
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->validate()) {
            return $this->staffs->edit($model->id, Yii::$app->user->identity->company_id, $form, null);
        }

        return $form;
    }

    /**
     * @return Staff|StaffCreateForm
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionCreate()
    {
        $company_id = Yii::$app->user->identity->company_id;

        $form = new StaffCreateForm($company_id);
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->validate()) {
            try {
                return $this->staffs->hire($company_id, $form, null);
            } catch (\DomainException $e) {
                throw new ServerErrorHttpException($e->getMessage());
            }
        }

        return $form;
    }

    /**
     * @param $id
     * @throws ServerErrorHttpException
     */
    public function actionFire($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        try {
            $this->staffs->fire($id);
            Yii::$app->getResponse()->setStatusCode(204);
        } catch (\DomainException $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return ServicesForm|array
     */
    public function actionAddServices($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new ServicesForm();

        if ($form->load(Yii::$app->request->bodyParams) && $form->validate()) {
            return $this->staffs->addServices($id, $form->services);
        }

        return $form;
    }

    /**
     * @param $id
     * @return ServicesForm|array
     */
    public function actionDeleteServices($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new ServicesForm();

        if ($form->load(Yii::$app->request->bodyParams) && $form->validate()) {
            return $this->staffs->deleteServices($id, $form->services);
        }

        return $form;
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

}

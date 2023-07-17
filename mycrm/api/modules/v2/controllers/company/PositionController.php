<?php

namespace api\modules\v2\controllers\company;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\company\CompanyPositionSearch;
use core\forms\company\CompanyPositionCreateForm;
use core\forms\company\CompanyPositionUpdateForm;
use core\models\company\CompanyPosition;
use core\repositories\exceptions\NotFoundException;
use core\services\company\CompanyPositionService;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class PositionController extends BaseController
{
    public $modelClass = 'core\models\company\CompanyPosition';
    private $companyPositionService;
    private $company_id;

    public function __construct(
        $id,
        $module,
        CompanyPositionService $companyPositionService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->companyPositionService = $companyPositionService;
    }

    public function beforeAction($action)
    {
        $beforeAction = parent::beforeAction($action);

        if(($identity = Yii::$app->user->identity) !== null) {
            $this->company_id = $identity->company_id;
        }

        return $beforeAction;
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
                        'create',
                        'view',
                        'update',
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
        $searchModel             = new CompanyPositionSearch();
        $searchModel->company_id = $this->company_id;

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string          $action
     * @param CompanyPosition $model
     * @param array           $params
     *
     * @throws NotFoundHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update'])) {
            if ($model->company_id !== $this->company_id || $model->deleted_time !== null) {
//                throw new ForbiddenHttpException('You are not allowed to act on this object');
                throw new NotFoundHttpException('The requested entity does not exist.');
            }
        }
    }

    /**
     * Updates an existing CompanyPosition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new CompanyPositionUpdateForm($model);
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->validate()) {
            return $this->companyPositionService->edit(
                $this->company_id,
                $model->id,
                $form->name,
                $form->description,
                $form->categories,
                $form->documentForms
            );
        }

        return $form;
    }

    /**
     * Creates a new CompanyPosition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $form = new CompanyPositionCreateForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->validate()) {
            return $this->companyPositionService->add(
                $this->company_id,
                $form->name,
                $form->description,
                $form->categories,
                $form->documentForms
            );
        }

        return $form;
    }

    protected function findModel($id)
    {
        try {
            return $this->companyPositionService->find($this->company_id, $id);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException('The requested entity does not exist.');
        }
    }
}

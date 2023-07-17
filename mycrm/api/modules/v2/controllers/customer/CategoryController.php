<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\customer\CustomerCategorySearch;
use core\models\customer\CustomerCategory;
use core\services\customer\CustomerCategoryService;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class CategoryController extends BaseController
{
    public $modelClass = 'core\models\customer\CustomerCategory';

    /**
     * @var CustomerCategoryService
     */
    private $service;

    public function __construct(
        string $id,
        $module,
        CustomerCategoryService $service,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->service = $service;
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
        $searchModel = new CustomerCategorySearch();

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
     * @return CustomerCategory
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new CustomerCategory();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->company_id = Yii::$app->user->identity->company_id;

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $model = $this->service->create($model->name, $model->company_id, $model->discount, $model->color);
        $model->refresh();

        return $model;
    }

    /**
     * @param $id
     * @return CustomerCategory
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id){
        $model = CustomerCategory::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Model not exists');
        }

        $model->load(Yii::$app->request->bodyParams, '');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $model = $this->service->update($model->id, $model->name, $model->discount, $model->color);

        return $model;
    }
}

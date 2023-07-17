<?php

namespace frontend\controllers;

use common\components\Model;
use core\models\division\DivisionService;
use core\models\Service;
use core\models\ServiceCategory;
use core\models\Staff;
use frontend\search\ServiceCategorySearch;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ServiceCategoryController implements the CRUD actions for ServiceCategory model.
 */
class ServiceCategoryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'update', 'search', 'delete', 'tree', 'list', 'edit'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['create', 'new'],
                        'allow'   => true,
                        'roles'   => ['serviceCategoryCreate'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all ServiceCategory models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new ServiceCategorySearch();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams,
            false
        );

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single ServiceCategory model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ServiceCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model           = new ServiceCategory();
        $model->image_id = 1;
        $model->company_id = Yii::$app->user->identity->compay_id;
        $services        = [new Service()];

        if ($model->load(Yii::$app->request->post())) {

            $services = Model::createMultiple(Service::className());
            Model::loadMultiple($services, Yii::$app->request->post());

            if ($model->validate() && Model::validateMultiple($services)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ( ! ($model->save()
                            && Service::saveMultiple($model, $services, []))
                    ) {
                        throw new \DomainException(
                            Yii::t('app', 'Invalid input data')
                        );
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash(
                        'success',
                        Yii::t('app', 'Error while creating')
                    );
                    return $this->redirect(['view', 'id' => $model->id]);
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t('app', 'Error while creating')
                );
            }
        }

        return $this->render('create', [
            'model'    => $model,
            'services' => $services,
        ]);
    }

    /**
     * Updates an existing ServiceCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('app', 'Successful saving')
            );

            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Lists services by filtering
     *
     * @return array
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Ajax staffs list
        $all_params = Yii::$app->request->post('depdrop_all_params', null);
        $staff_id = isset($all_params['order-staff_id']) ? $all_params['order-staff_id'] : null;
        $insurance_company_id = isset($all_params['order-insurance_company_id']) ? $all_params['order-insurance_company_id'] : null;

        if (empty($staff_id)) {
            return ['output' => '', 'selected' => ''];
        }

        $staff = Staff::findOne($staff_id);
        if ($staff === null) {
            return ['output' => '', 'selected' => ''];
        }

        $divisionServicesQuery = DivisionService::find()
            ->joinWith(['categories', 'staffs'])
            ->where(['{{%staffs}}.id' => $staff->id])
            ->deleted(false)
            ->orderBy('{{%service_categories}}.name ASC, {{%division_services}}.service_name');

        if ( ! empty($insurance_company_id)) {
            $divisionServicesQuery->andWhere([
                'OR',
                ['insurance_company_id' => null],
                ['insurance_company_id' => $insurance_company_id],
            ]);
        } else {
            $divisionServicesQuery->andWhere(['insurance_company_id' => null]);
        }

        /* @var DivisionService[] $divisionServices */
        $divisionServices = $divisionServicesQuery->all();
        $out = [];
        foreach ($divisionServices as $divisionService) {
            $categories = $divisionService->categories;
            if (empty($categories)) {
                $out[] = [
                    'id'      => $divisionService->id,
                    'name'    => $divisionService->getFullName(),
                    'options' => [
                        'data-price'        => $divisionService->price,
                        'data-duration'     => $divisionService->average_time,
                        'data-service_name' => $divisionService->service_name
                    ]
                ];
            } else {
                foreach ($categories as $category) {
                    /* @var ServiceCategory $category */
                    $out[$category->name][] = [
                        'id'      => $divisionService->id,
                        'name'    => $divisionService->getFullName(),
                        'options' => [
                            'data-price'        => $divisionService->price,
                            'data-duration'     => $divisionService->average_time,
                            'data-service_name' => $divisionService->service_name
                        ]
                    ];
                }
            }
        }

        $params   = Yii::$app->request->post('depdrop_params', null);
        $selected = '';
        if ($params != null && $params[0]) {
            $selected .= $params[0];
        }

        return ['output' => $out, 'selected' => $selected];
    }

    /**
     * @return string
     */
    public function actionTree()
    {
        return $this->renderPartial('tree');
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $model = new ServiceCategory();
        $model->company_id = Yii::$app->user->identity->company_id;
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return JSON::encode(["errors" => $model->errors]);
        }
        return $this->renderAjax('form', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("serviceCategoryUpdate", ['model' => $model])) {
            throw new ForbiddenHttpException();
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return JSON::encode(["errors" => $model->errors]);
        }
        return $this->renderAjax('form', ['model' => $model]);
    }

    /**
     * Deletes an existing ServiceCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if (!Yii::$app->user->can("serviceCategoryDelete", ['model' => $model])) {
            throw new ForbiddenHttpException();
        }

        try {
            if ($model->disable()) {
                Yii::$app->response->setStatusCode('200');
                return "success";
            } else {
                Yii::$app->response->setStatusCode('400');
                return "error";
            }
        } catch (Exception $e) {
            Yii::$app->response->setStatusCode('500');
            return "error";
        }
    }

    /**
     * Search categories
     * @return array
     */
    public function actionList()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $params = \Yii::$app->request->post('depdrop_all_params');
        $division = empty($params['division']) ? Yii::$app->user->identity->permittedDivisions: $params['division'];
        $data = ['output' => []];

        if ($division) {
            $items = ServiceCategory::find()
                ->select(['{{%service_categories}}.id', "{{%service_categories}}.name"])
                ->byDivision($division)
                ->orderBy('name ASC')
                ->asArray()
                ->all();

            $data['output'] = $items;
        }

        return $data;
    }

    /**
     * Finds the ServiceCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ServiceCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ServiceCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

<?php

namespace frontend\modules\customer\controllers;

use core\models\customer\CustomerSubscription;
use core\models\customer\CustomerSubscriptionService;
use Exception;
use frontend\modules\customer\components\CustomerModuleController;
use frontend\modules\customer\search\SubscriptionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * SubscriptionController implements the CRUD actions for CustomerSubscription model.
 */
class SubscriptionController extends CustomerModuleController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => [
                            'index', 'create', 'update'
                        ],
                        'roles'   => ['companyCustomerSubscriptionAdmin'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post']
                ],
            ],
        ];
    }

    /**
     * Lists all CustomerSubscription models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new SubscriptionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CustomerSubscription model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CustomerSubscription model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new CustomerSubscription();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $model->services_ids = json_decode($model->services_ids);
                    if (!empty($model->services_ids)) {
                        foreach ($model->services_ids as $key => $service_id) {
                            $newService                      = new CustomerSubscriptionService();
                            $newService->subscription_id     = $model->id;
                            $newService->division_service_id = $service_id;
                            if (!$newService->save()) {
                                throw new Exception("Произошла ошибка при сохранении");
                            }
                        }
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollback();
                $model->addError('services_ids', $e->getMessage());
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CustomerSubscription model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $model->services_ids = json_decode($model->services_ids);
                    $oldServices         = \yii\helpers\ArrayHelper::getColumn($model->services, 'division_service_id');
                    $servicesToDelete    = array_diff($oldServices, $model->services_ids);
                    $servicesToSave      = array_diff($model->services_ids, $oldServices);

                    if (!empty($servicesToDelete)) {
                        CustomerSubscriptionService::deleteAll([
                            'division_service_id' => $servicesToDelete,
                            'subscription_id' => $model->id
                        ]);
                    }
                    if (!empty($servicesToSave)) {
                        foreach ($servicesToSave as $key => $service_id) {
                            $newService                      = new CustomerSubscriptionService();
                            $newService->subscription_id     = $model->id;
                            $newService->division_service_id = $service_id;
                            if (!$newService->save()) {
                                throw new Exception("Произошла ошибка при сохранении");
                            }
                        }
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollback();
                $model->addError('services_ids', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CustomerSubscription model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
//        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CustomerSubscription model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CustomerSubscription the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = CustomerSubscription::find()->company()->andWhere(['crm_customer_subscriptions.id' => $id])->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

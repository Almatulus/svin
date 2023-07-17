<?php

namespace frontend\modules\finance\controllers;

use core\services\finance\CostItemCategoryService;
use Yii;
use core\models\finance\CompanyCostItemCategory;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CostItemCategoryController implements the CRUD actions for CompanyCostItemCategory model.
 */
class CostItemCategoryController extends Controller
{
    private $service;

    /**
     * CostItemController constructor.
     *
     * @param string                  $id
     * @param \yii\base\Module        $module
     * @param CostItemCategoryService $service
     * @param array                   $config
     */
    public function __construct(
        $id,
        $module,
        CostItemCategoryService $service,
        $config = []
    ) {
        $this->service = $service;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => ['companyCostItemAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CompanyCostItemCategory models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CompanyCostItemCategory::find()->company(),
            'sort'  => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyCostItemCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $model = new CompanyCostItemCategory();

        if ($model->load(Yii::$app->request->post())) {
            try {
                $model = $this->service->create($model);
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CompanyCostItemCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            try {
                $model = $this->service->update($model);
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CompanyCostItemCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id);
        $this->service->delete($id);
        return $this->redirect(['index']);
    }

    /**
     * Finds the CompanyCostItemCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyCostItemCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyCostItemCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app',
            'The requested page does not exist.'));
    }
}

<?php

namespace frontend\modules\finance\controllers;

use core\models\finance\CompanyContractor;
use frontend\modules\finance\components\FinanceController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * CompanyContractorController implements the CRUD actions for CompanyContractor model.
 */
class ContractorController extends FinanceController
{
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
                        'actions' => [
                            'index',
                            'create',
                            'update',
                            'delete',
                            'new'
                        ],
                        'allow'   => true,
                        'roles'   => ['companyContractorAdmin'],
                    ],
                    [
                        'actions' => ['new'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CompanyContractor models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CompanyContractor::find()->division(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyContractor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CompanyContractor();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful saving'));

            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CompanyContractor model.
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
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful saving'));

            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CompanyContractor model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('error',
            Yii::t('app', 'Successful delete {something}',
                ['something' => $model->name])
        );

        return $this->redirect(['index']);
    }

    public function actionNew()
    {
        $model = new CompanyContractor();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return json_encode([
                    'status' => "success",
                    'data'   => $model->attributes
                ]);
            } else {
                return json_encode(['errors' => $model->errors]);
            }
        }

        return $this->renderAjax('new', ['model' => $model]);
    }

    /**
     * Finds the CompanyContractor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyContractor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = CompanyContractor::find()
                                  ->division()
                                  ->andWhere(['id' => $id])
                                  ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

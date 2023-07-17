<?php

namespace frontend\modules\medCard\controllers;

use core\models\medCard\MedCardToothDiagnosis;
use frontend\modules\medCard\search\MedCardToothDiagnosisSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * TeethDiagnosisController implements the CRUD actions for MedCardTeethDiagnosis model.
 */
class TeethDiagnosisController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'create',
                            'update',
                            'delete'
                        ],
                        'allow'   => true,
                        'roles'   => ['teethDiagnosisAdmin'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all MedCardTeethDiagnosis models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel             = new MedCardToothDiagnosisSearch();
        $searchModel->company_id = Yii::$app->user->identity->company_id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new MedCardTeethDiagnosis model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model             = new MedCardToothDiagnosis();
        $model->company_id = Yii::$app->user->identity->company_id;;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MedCardTeethDiagnosis model.
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
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
            return $this->redirect(['index', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MedCardTeethDiagnosis model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (empty($model->medCardTeeth)) {
            $model->delete();
            Yii::$app->session->setFlash(
                'success',
                Yii::t(
                    'app',
                    'Successful delete {something}',
                    ['something' => $model->name]
                )
            );
        } else {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app', 'Delete Error')
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the MedCardTeethDiagnosis model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return MedCardToothDiagnosis the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MedCardToothDiagnosis::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

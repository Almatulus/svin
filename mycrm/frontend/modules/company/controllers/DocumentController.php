<?php

namespace frontend\modules\company\controllers;

use core\models\Image;
use core\models\order\OrderDocumentTemplate;
use frontend\modules\company\search\CompanyDocumentSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * CompanyDocumentController implements the CRUD actions for CompanyDocument model.
 */
class DocumentController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => ['documentTemplateAdmin'],
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
     * Lists all CompanyDocument models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyDocumentSearch();
        $searchModel->company_id = Yii::$app->user->identity->company_id;
        $dataProvider
            = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyDocument model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model             = new OrderDocumentTemplate();
        $company = Yii::$app->user->identity->company;
        $model->company_id = $company->id;
        $model->category_id=$company->category_id;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $imageFile = UploadedFile::getInstance($model, 'path');
            $image     = $imageFile ? Image::uploadImage($imageFile) : null;

            if ( ! $image) {
                $model->addError('path',
                    Yii::t('app', '{attribute} cannot be blank.',
                        ['attribute' => Yii::t('app', 'Path')])
                );
            } else {
                $model->path = $image->getPath();
                if ($model->save()) {
                    return $this->redirect(['index']);
                }
            }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CompanyDocument model.
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
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CompanyDocument model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CompanyDocument model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return OrderDocumentTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = OrderDocumentTemplate::findOne([
            'id'         => $id,
            'company_id' => Yii::$app->user->identity->company_id
        ]);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

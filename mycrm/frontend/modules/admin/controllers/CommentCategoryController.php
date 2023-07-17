<?php

namespace frontend\modules\admin\controllers;

use core\models\ServiceCategory;
use frontend\modules\admin\search\MedCardCommentCategorySearch;
use Yii;
use core\models\medCard\MedCardCommentCategory;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CommentTemplateCategoryController implements the CRUD actions for CommentTemplateCategory model.
 */
class CommentCategoryController extends Controller
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
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [MedCardCommentCategory::getViewPermissionName()],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => [MedCardCommentCategory::getUpdatePermissionName()],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => [MedCardCommentCategory::getCreatePermissionName()],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => [MedCardCommentCategory::getDeletePermissionName()],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CommentTemplateCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MedCardCommentCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $categories = MedCardCommentCategory::find()->all();
        return $this->render('index', compact('searchModel', 'dataProvider', 'categories'));
    }

    /**
     * Creates a new CommentTemplateCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MedCardCommentCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully created'));
            return $this->redirect(['index']);
        }

        $categories = MedCardCommentCategory::find()->all();
        $serviceCategories = ServiceCategory::find()->root()->all();
        return $this->render('create', compact('model', 'categories', 'serviceCategories'));
    }

    /**
     * Updates an existing CommentTemplateCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully updated'));
            return $this->redirect(['index']);
        }

        $categories = MedCardCommentCategory::find()->all();
        $serviceCategories = ServiceCategory::find()->root()->all();
        return $this->render('update', compact('model', 'categories', 'serviceCategories'));
    }

    /**
     * Deletes an existing CommentTemplateCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully deleted'));

        return $this->redirect(['index']);
    }

    /**
     * Finds the CommentTemplateCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return MedCardCommentCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MedCardCommentCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

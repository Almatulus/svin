<?php

namespace frontend\modules\admin\controllers;

use core\models\medCard\MedCardCommentCategory;
use core\models\medCard\MedCardDiagnosis;
use Yii;
use core\models\medCard\MedCardComment;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CommentTemplateController implements the CRUD actions for CommentTemplate model.
 */
class CommentController extends Controller
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
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => [MedCardComment::getUpdatePermissionName()],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => [MedCardComment::getCreatePermissionName()],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => [MedCardComment::getDeletePermissionName()],
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
     * Creates a new CommentTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MedCardComment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully created'));
            return $this->redirect(['comment-category/index']);
        }

        $categories = MedCardCommentCategory::find()->all();
        $diagnoses = MedCardDiagnosis::find()->all();
        return $this->render('create', compact('model', 'categories', 'diagnoses'));
    }

    /**
     * Updates an existing CommentTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully updated'));
            return $this->redirect(['comment-category/index']);
        }

        $categories = MedCardCommentCategory::find()->all();
        $diagnoses = MedCardDiagnosis::find()->all();
        return $this->render('update', compact('model', 'categories', 'diagnoses'));
    }

    /**
     * Deletes an existing CommentTemplate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully deleted'));

        return $this->redirect(['comment-category/index']);
    }

    /**
     * Finds the CommentTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return MedCardComment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MedCardComment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

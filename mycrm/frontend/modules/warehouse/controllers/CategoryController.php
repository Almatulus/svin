<?php

namespace frontend\modules\warehouse\controllers;

use core\forms\warehouse\category\CategoryCreateForm;
use core\forms\warehouse\category\CategoryUpdateForm;
use core\models\warehouse\Category;
use core\services\warehouse\CategoryService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    private $service;

    public function __construct($id, $module, CategoryService $categoryService, $config = [])
    {
        $this->service = $categoryService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['tree', 'new', 'edit', 'delete', 'list'],
                'rules' => [
                    [
                        'actions' => ['tree', 'new', 'edit', 'delete'],
                        'allow' => true,
                        'roles' => ['warehouseAdmin'],
                    ],
                    [
                        'actions' => ['list'],
                        'allow'   => true,
                        'roles'   => ['@'],
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

    public function actionTree()
    {
        return $this->renderPartial('tree');
    }

    public function actionNew()
    {
        $form = new CategoryCreateForm();
        if (Yii::$app->request->isPost) {
            if ($form->load(Yii::$app->request->post()) && $form->validate()) {
                $model = $this->service->create($form->name, Yii::$app->user->identity->company_id);
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return Json::encode(["errors" => $form->errors]);
        }
        return $this->renderAjax('form', ['model' => $form]);
    }

    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $form = new CategoryUpdateForm($model);
        if (Yii::$app->request->isPost) {
            if ($form->load(Yii::$app->request->post()) && $form->validate()) {
                $this->service->edit($id, $form->name);
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return Json::encode(["errors" => $form->errors]);
        }
        return $this->renderAjax('form', ['model' => $form]);
    }

    /**
     * Deletes an existing ServiceCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                Yii::$app->response->setStatusCode('200');
                return Json::encode("success");
            } else {
                Yii::$app->response->setStatusCode('400');
                return Json::encode("error");
            }
        } catch (Exception $e) {
            Yii::$app->response->setStatusCode('500');
            return Json::encode("error");
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
        $division = $params['division_id'];
        $data = ['output' => []];

        if ($division) {
            $items = Category::find()
                ->select(['{{%warehouse_category}}.id', "{{%warehouse_category}}.name"])
                ->joinWith('products', false)
                ->andWhere(['division_id' => $division])
                ->orderBy('name ASC')
                ->asArray()
                ->all();

            $data['output'] = $items;
        }

        return $data;
    }
    
    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Category::find()->company()->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

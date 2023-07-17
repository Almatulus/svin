<?php

namespace frontend\modules\finance\controllers;

use core\forms\finance\CostItemForm;
use core\forms\finance\CostItemUpdateForm;
use core\models\finance\CompanyCostItem;
use core\services\CompanyCostItemService;
use frontend\modules\finance\components\FinanceController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * CostItemController implements the CRUD actions for CompanyCostItem model.
 */
class CostItemController extends FinanceController
{
    private $costItemService;

    /**
     * CostItemController constructor.
     *
     * @param string                 $id
     * @param \yii\base\Module       $module
     * @param CompanyCostItemService $costItemService
     * @param array                  $config
     */
    public function __construct(
        $id,
        $module,
        CompanyCostItemService $costItemService,
        $config = []
    ) {
        $this->costItemService = $costItemService;
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
     * Lists all CompanyCostItem models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CompanyCostItem::find()->company()->permitted(),
            'sort'  => [
                'defaultOrder' => [
                    'is_deletable' => SORT_DESC,
                    'id'           => SORT_DESC
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyCostItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new CostItemForm();

        if ($form->load(Yii::$app->request->post())) {
            if ($form->validate()) {
                try {
                    $this->costItemService->add(
                        $form->comments,
                        $form->company_id,
                        $form->name,
                        $form->type,
                        $form->divisions,
                        $form->category_id
                    );
                    Yii::$app->session->setFlash(
                        'success',
                        Yii::t('app', 'Successful saving')
                    );

                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                $errors = $form->getErrors();
                Yii::$app->session->setFlash('error', reset($errors)[0]);
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates a new CompanyCostItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form  = new CostItemUpdateForm($model);

        if ($form->load(Yii::$app->request->post())) {
            if ($form->validate()) {
                try {
                    $this->costItemService->edit(
                        $id,
                        $form->comments,
                        $form->company_id,
                        $form->name,
                        $form->type,
                        $form->divisions,
                        $form->category_id
                    );
                    Yii::$app->session->setFlash(
                        'success',
                        Yii::t('app', 'Successful saving')
                    );

                    return $this->redirect(['update', 'id' => $id]);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                $errors = $form->getErrors();
                Yii::$app->session->setFlash('error', reset($errors)[0]);
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing CompanyCostItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->canBeDeleted()) {
            Yii::$app->session->setFlash('error',
                Yii::t('app', 'Delete Error') . ". " .
                Yii::t('app', 'Given cost item has cashflows'));
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful delete {something}',
                    ['something' => $model->name]));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the CompanyCostItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyCostItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /* @var CompanyCostItem $model */
        $model = CompanyCostItem::find()
                                ->company()
                                ->deletable()
                                ->andWhere(['id' => $id])
                                ->one();

        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }
}

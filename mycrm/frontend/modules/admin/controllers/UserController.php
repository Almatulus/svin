<?php

namespace frontend\modules\admin\controllers;

use core\forms\user\UserForm;
use core\forms\user\UserUpdateForm;
use core\helpers\user\UserHelper;
use core\models\user\User;
use frontend\modules\admin\search\UserSearch;
use core\services\user\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    private $userService;

    /**
     * UserController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param UserService $userService
     * @param array $config
     */
    public function __construct($id, $module, UserService $userService, $config = [])
    {
        $this->userService = $userService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'delete', 'password'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['userView'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['userCreate'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['userDelete'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new UserForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $model = $this->userService->create(
                    $form->company_id,
                    $form->password,
                    $form->role,
                    $form->status,
                    $form->username,
                    $form->user_permissions
                );
                UserHelper::invalidateMainMenuCache($model->id);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form  = new UserUpdateForm($model);

        if (!Yii::$app->user->can('userUpdate', ['model' => $model])) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->userService->edit(
                    $model->id,
                    $form->company_id,
                    $form->password,
                    $form->role,
                    $form->status,
                    $form->username,
                    $form->user_permissions
                );
                UserHelper::invalidateMainMenuCache($model->id);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->refresh();
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

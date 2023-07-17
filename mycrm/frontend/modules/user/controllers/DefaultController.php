<?php

namespace frontend\modules\user\controllers;

use core\forms\PasswordForm;
use core\services\user\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class DefaultController extends Controller
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
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'delete', 'password'],
                'rules' => [
                    [
                        'actions' => ['password'],
                        'allow' => true,
                        'roles' => ['@'],
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
     * Updates user password
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPassword()
    {
        $model = new PasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->userService->editPassword(Yii::$app->user->id, $model->new_password);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->redirect(['password']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('password', [
            'model' => $model,
        ]);
    }
}

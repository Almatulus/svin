<?php

namespace frontend\controllers;

use common\components\events\PushNotificationEventHandler;
use common\components\PushNotification;
use core\forms\LoginForm;
use core\forms\RestoreForm;
use core\helpers\user\UserLogHelper;
use core\models\customer\Customer;
use core\services\user\AuthService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;

class AuthController extends Controller
{
    public $layout = '//main-login';
    private $service;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'validate-key' => ['post'],
                    'validate-phone' => ['post'],
                    'reset-password' => ['post']
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function __construct(string $id, $module, AuthService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionLogin()
    {
        if ( ! \Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->userLogger->create(
                $model->username,
                UserLogHelper::ACTION_LOGGED_IN,
                Yii::$app->request->getUserIP(),
                Yii::$app->request->getUserAgent()
            );
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionRestore()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new RestoreForm();
        return $this->render('restore', [
            'model' => $model
        ]);
    }

    /**
     * @return string
     */
    public function actionValidateKey()
    {
        $phone = Yii::$app->request->getBodyParam('login');
        $code  = Yii::$app->request->getBodyParam('key');

        $model           = new RestoreForm();
        $model->scenario = RestoreForm::SCENARIO_CODE;
        $model->phone    = $phone;
        $model->code     = $code;

        if ($model->validate()) {
            Yii::$app->response->setStatusCode('200');
            return Json::encode(['status' => 'OK']);
        }

        Yii::$app->response->setStatusCode('400');
        return Json::encode(['status' => 'ERROR', 'message' => 'Неверный ключ']);
    }

    /**
     * @return string
     */
    public function actionValidatePhone()
    {
        $phone   = Yii::$app->request->getBodyParam('login');
        $captcha = Yii::$app->request->getBodyParam('captcha');

        $model            = new RestoreForm();
        $model->phone     = $phone;
        $model->reCaptcha = $captcha;
        $model->scenario  = RestoreForm::SCENARIO_PHONE;

        if ($model->validate()) {
            $model->generateForgotHash();
            $model->sendCode();
            Yii::$app->response->setStatusCode('200');
            return Json::encode(['status' => 'OK']);
        }

        Yii::$app->response->setStatusCode('400');
        return Json::encode(['status' => 'ERROR', 'message' => $model->errorMessage]);
    }

    /**
     * @return string
     */
    public function actionResetPassword()
    {
        $phone      = Yii::$app->request->getBodyParam('login');
        $password   = Yii::$app->request->getBodyParam('password');
        $repassword = Yii::$app->request->getBodyParam('repassword');

        $model             = new RestoreForm();
        $model->scenario   = RestoreForm::SCENARIO_PASS;
        $model->password   = $password;
        $model->repassword = $repassword;
        $model->phone      = $phone;
        if ($model->validate()) {
            if ($model->recovery()) {
                $model->resetForgotHash();
                Yii::$app->response->setStatusCode('200');
                return Json::encode(['status' => 'OK', 'message' => "Сброс пароля выполнен успешно."]);
            }
        }
        Yii::$app->response->setStatusCode('400');
        return Json::encode(['status' => 'ERROR', 'message' => $model->errorMessage]);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionLogout()
    {
        $username = Yii::$app->user->identity->username;

        $this->service->logout(Yii::$app->user->id);

        Yii::$app->user->logout();

        Yii::$app->userLogger->create(
            $username,
            UserLogHelper::ACTION_LOGGED_OUT,
            Yii::$app->request->getUserIP(),
            Yii::$app->request->getUserAgent()
        );

        return $this->goHome();
    }

    public function actionNotify($callback)
    {
        $customer = Customer::findOne(["id" => 10]);
        PushNotification::sendNotification(
            new PushNotificationEventHandler([
                'title' => "Запись в 'Тестовое заведение'",
                'message' => 'Ваша запись на «Мужская стрижка» '
                    . 'в «Тестовый салон» '
                    . 'по адресу «Заводска 321» перенесена '
                    . 'с «2016-07-02 10:00» '
                    . 'на «2016-07-02 11:00» ',
                'customer' => $customer,
                'division_id' => 49,
                'callback' => $callback,
            ])
        );
    }
}

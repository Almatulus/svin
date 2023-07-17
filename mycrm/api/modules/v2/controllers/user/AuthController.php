<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\OptionsTrait;
use core\forms\LoginForm;
use core\forms\user\ForgotPasswordForm;
use core\forms\user\ResetPasswordForm;
use core\forms\user\ValidatePasswordCodeForm;
use core\helpers\user\UserLogHelper;
use core\services\user\AuthService;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;

class AuthController extends Controller
{
    use OptionsTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'only' => ['logout', 'user'],
        ];

        return $behaviors;
    }

    private $auth;

    public function __construct(
        $id,
        $module,
        AuthService $auth,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->auth = $auth;
    }

    /**
     * @param $action
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($action);
    }

    /**
     * Log in user and return token
     *
     * @return array|LoginForm
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $form = new LoginForm();
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ( ! $form->validate()) {
            return $form;
        }

        \Yii::$app->userLogger->create(
            $form->username,
            UserLogHelper::ACTION_LOGGED_IN,
            \Yii::$app->request->getUserIP(),
            \Yii::$app->request->getUserAgent()
        );

        return ['token' => $this->auth->getAccessToken($form->username)];
    }

    /**
     * Logs out user
     */
    public function actionLogout()
    {
        $username = Yii::$app->user->identity->username;

        $this->auth->logout(Yii::$app->user->id);

        Yii::$app->user->logout();

        Yii::$app->userLogger->create(
            $username,
            UserLogHelper::ACTION_LOGGED_OUT,
            Yii::$app->request->getUserIP(),
            Yii::$app->request->getUserAgent()
        );

        return [];
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionForgotPassword()
    {
        $form = new ForgotPasswordForm();
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ( ! $form->validate()) {
            return $form;
        }

        $this->auth->sendConfirmKey($form->username);

        return [];
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionValidateCode()
    {
        $form = new ValidatePasswordCodeForm();
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ( ! $form->validate()) {
            return $form;
        }

        return [];
    }

    /**
     * @return array|ResetPasswordForm
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionChangePassword()
    {
        $form = new ResetPasswordForm();
        $form->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ( ! $form->validate()) {
            return $form;
        }

        return ['token' => $this->auth->changePassword($form->username, $form->code, $form->password)];
    }

    public function actionUser()
    {
        return Yii::$app->user->identity;
    }
}

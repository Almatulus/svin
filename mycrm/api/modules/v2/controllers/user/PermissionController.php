<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\OptionsTrait;
use core\helpers\StaffHelper;
use core\helpers\user\UserHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;

class PermissionController extends Controller
{
    use OptionsTrait;

    public function actionIndex()
    {
        $cache   = Yii::$app->cache;
        $user_id = Yii::$app->user->id;
        $key     = UserHelper::getMainMenuCacheKey($user_id);

        $menu_items = $cache->get($key);
        if ($menu_items === false) {
            $menu_items = UserHelper::invalidateMainMenuCache($user_id);
        }

        return $menu_items;
    }

    public function actionOptions()
    {
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Yii::$app->getResponse()->setStatusCode(405);
        }

        $options = ['GET', 'HEAD', 'OPTIONS'];
        Yii::$app->getResponse()->getHeaders()
            ->set('Allow', implode(', ', $options));
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => ['index', 'options'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
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
}

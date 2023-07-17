<?php

namespace console\controllers;

use core\helpers\user\UserHelper;
use core\models\user\User;
use yii\console\Controller;

class UserController extends Controller
{
    /**
     * @param int|null $user_id
     */
    public function actionInvalidateMenu(int $user_id = null)
    {
        $users = User::find()->andFilterWhere(['id' => $user_id])->select(['id'])->asArray()->column();

        $cache = \Yii::$app->cache;
        foreach ($users as $user_id) {
            $key = UserHelper::getMainMenuCacheKey($user_id);
            if ($cache->get($key) !== false) {
                UserHelper::invalidateMainMenuCache($user_id);
                echo "{$user_id}" . PHP_EOL;
            }
        }
    }
}
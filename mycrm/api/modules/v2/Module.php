<?php

namespace api\modules\v2;

use core\models\ApiHistory;
use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'api\modules\v2\controllers';
    private $start_time;

    public function beforeAction($action)
    {
        $this->start_time = microtime(true);
        return parent::beforeAction($action);
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed            $result
     *
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function afterAction($action, $result)
    {
        $result   = parent::afterAction($action, $result);

        ApiHistory::log(
            Yii::$app->request,
            Yii::$app->response,
            microtime(true) - $this->start_time,
            Yii::$app->user->isGuest ? null : Yii::$app->user->identity
        );

        return $result;
    }
}

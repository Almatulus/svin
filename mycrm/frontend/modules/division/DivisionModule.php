<?php

namespace frontend\modules\division;

/**
 * division module definition class
 */
class DivisionModule extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\division\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        DivisionAssets::register(\Yii::$app->view);
    }
}

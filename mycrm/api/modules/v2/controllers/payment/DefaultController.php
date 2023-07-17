<?php

namespace api\modules\v2\controllers\payment;

use api\modules\v2\OptionsTrait;
use api\modules\v2\search\payment\PaymentSearch;
use Yii;
use yii\rest\ActiveController;

class DefaultController extends ActiveController
{
    public $modelClass = 'core\models\Payment';

    use OptionsTrait;

    public function beforeAction($event)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($event);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider'
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new PaymentSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

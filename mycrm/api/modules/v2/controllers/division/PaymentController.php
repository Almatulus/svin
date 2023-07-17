<?php

namespace api\modules\v2\controllers\division;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\division\DivisionPaymentSearch;
use Yii;
use yii\filters\AccessControl;

class PaymentController extends BaseController
{
    public $modelClass = 'core\models\Payment';


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
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'options'
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new DivisionPaymentSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }
}
<?php

namespace api\modules\v2\controllers\division;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\division\DivisionServiceSearch;
use Yii;
use yii\filters\AccessControl;

class ServiceController extends BaseController
{
    public $modelClass = 'core\models\division\DivisionService';

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
                        'view'
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
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new DivisionServiceSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

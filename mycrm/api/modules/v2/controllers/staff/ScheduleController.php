<?php

namespace api\modules\v2\controllers\staff;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\staff\ScheduleSearch;
use Yii;
use yii\filters\AccessControl;

/**
 * @TODO
 */
class ScheduleController extends BaseController
{
    public $modelClass = 'core\models\StaffSchedule';

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
                    'actions' => ['index', 'options'],
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
        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\web\BadRequestHttpException
     */
    public function prepareDataProvider()
    {
        $searchModel              = new ScheduleSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

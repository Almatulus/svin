<?php

namespace api\modules\v2\controllers\common;

use api\modules\v2\OptionsTrait;
use api\modules\v2\search\common\ScheduleSearch;
use yii\rest\ActiveController;

class ScheduleController extends ActiveController
{
    use OptionsTrait;

    public $modelClass = false;

    public function beforeAction($action)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($action);
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
        $searchModel = new ScheduleSearch();
        return $searchModel->search(\Yii::$app->request->queryParams);
    }
}

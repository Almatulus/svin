<?php

namespace api\modules\v2\controllers\newsLog;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\newsLog\NewsLogSearch;
use api\modules\v2\OptionsTrait;
use Yii;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\NewsLog';

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
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new NewsLogSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}
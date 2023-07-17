<?php

namespace api\modules\v2\controllers\service;

use api\modules\v2\OptionsTrait;
use api\modules\v2\search\service\ServiceCategorySearch;
use Yii;
use yii\rest\ActiveController;

class RootCategoryController extends ActiveController
{
    public $modelClass = 'core\models\ServiceCategory';

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
        $searchModel = new ServiceCategorySearch();
        $searchModel->is_root = true;
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

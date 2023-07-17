<?php

namespace api\modules\v2\controllers\division;

use api\modules\v2\search\division\DivisionSearch;
use api\modules\v2\OptionsTrait;
use Yii;
use yii\rest\ActiveController;

class DefaultController extends ActiveController
{
    public $modelClass = 'core\models\division\Division';

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
        $searchModel = new DivisionSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

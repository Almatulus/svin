<?php

namespace api\modules\v2\controllers\staff;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\staff\StaffSearch;
use Yii;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\Staff';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = ['index', 'options', 'view'];

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
        $searchModel = new StaffSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

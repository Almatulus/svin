<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\customer\ContactSearch;

class ContactController extends BaseController
{
    public $modelClass = 'core\models\customer\CustomerContact';

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
        $searchModel = new ContactSearch();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }
}
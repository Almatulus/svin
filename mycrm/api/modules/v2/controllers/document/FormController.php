<?php

namespace api\modules\v2\controllers\document;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\OptionsTrait;
use api\modules\v2\search\document\DocumentFormSearch;

class FormController extends BaseController
{
    public $modelClass = 'core\models\document\DocumentForm';

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
        $searchModel = new DocumentFormSearch();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }
}
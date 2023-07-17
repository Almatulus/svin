<?php

namespace api\modules\v2\controllers\document;

use api\modules\v2\controllers\BaseController;
use core\models\document\DocumentSuggestion;

class SuggestionsController extends BaseController
{
    public $modelClass = false;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;

    }

    public function actionIndex()
    {
        $params = \Yii::$app->request->queryParams;

        $documents = DocumentSuggestion::find()->limit(10);

        if ($params['s']) {
            $documents->andWhere(['like', 'lower({{%document_suggestions}}.text)', mb_strtolower($params['s'])]);
        }
        return $documents->all();
    }
}
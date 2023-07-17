<?php

namespace api\modules\v2\controllers\diagnosis;

use api\modules\v2\search\diagnose\MedCardDiagnosisSearch;
use api\modules\v2\OptionsTrait;
use Yii;
use yii\rest\ActiveController;

class DefaultController extends ActiveController
{
    use OptionsTrait;

    public $modelClass = 'core\models\medCard\MedCardDiagnosis';

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
        $searchModel = new MedCardDiagnosisSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

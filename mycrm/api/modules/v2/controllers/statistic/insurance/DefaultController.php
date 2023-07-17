<?php

namespace api\modules\v2\controllers\statistic\insurance;

use api\modules\v2\controllers\BaseController;
use core\forms\statistic\InsuranceStatForm;
use core\services\StatisticService;
use yii\base\Module;

class DefaultController extends BaseController
{
    public $modelClass = false;

    /** @var StatisticService */
    private $service;

    /**
     * DefaultController constructor.
     * @param string $id
     * @param Module $module
     * @param StatisticService $service
     * @param array $config
     */
    public function __construct($id, Module $module, StatisticService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
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
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new InsuranceStatForm();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * Exports Excel File
     */
    public function actionExport()
    {
        $dataProvider = $this->prepareDataProvider();

        $this->service->exportInsurance($dataProvider->getModels());
    }
}
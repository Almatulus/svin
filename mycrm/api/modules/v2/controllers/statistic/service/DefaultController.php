<?php
/**
 * Created by PhpStorm.
 * User: Tumar
 * Date: 30.01.2018
 * Time: 15:07
 */

namespace api\modules\v2\controllers\statistic\service;

use api\modules\v2\controllers\BaseController;
use core\forms\customer\statistic\StatisticService;
use yii\data\ActiveDataProvider;
use yii\base\Module;
use yii\web\UnprocessableEntityHttpException;


class DefaultController extends BaseController
{
    public $modelClass = false;

    /** @var \core\services\StatisticService */
    private $service;

    /**
     * DefaultController constructor.
     * @param string $id
     * @param Module $module
     * @param \core\services\StatisticService $service
     * @param array $config
     */
    public function __construct($id, Module $module, \core\services\StatisticService $service, array $config = [])
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
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $model = new StatisticService();
        $model->load(\Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'revenue',
                    'orders_count',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        $models = $dataProvider->getModels();
        $totalRevenue = $dataProvider->query->sum('revenue');

        foreach ($models as $model) {
            $model->revenueShare = $totalRevenue == 0 ? 0 : $model->revenue / $totalRevenue * 100;
        }

        return $dataProvider;
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    public function actionExport()
    {
        $model = new StatisticService();
        $model->load(\Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'revenue',
                    'orders_count',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        $dataProvider->pagination = false;

        $models = $dataProvider->models;

        if (empty($models)) {
            throw new UnprocessableEntityHttpException('Empty models');
        }

        $this->service->exportServices($model, $models);
    }

    /**
     * @return array
     */
    public function actionTop()
    {
        $model = new StatisticService();
        $model->load(\Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'revenue',
                    'orders_count',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        $models = $dataProvider->getModels();

        return $model->getTop($models);
    }
}
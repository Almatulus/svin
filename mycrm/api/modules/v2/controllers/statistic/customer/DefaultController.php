<?php
namespace api\modules\v2\controllers\statistic\customer;

use api\modules\v2\controllers\BaseController;
use core\forms\customer\statistic\StatisticCustomer;
use yii\data\ActiveDataProvider;

class DefaultController extends BaseController
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

    /**
     * @return array
     */
    public function actionIndex()
    {
        $model = new StatisticCustomer();
        $model->load(\Yii::$app->request->get(), '');

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'average_revenue',
                    'revenue',
                    'orders_count',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        $models = $dataProvider->getModels();
        $totalRevenue = $dataProvider->query->sum('revenue');

        foreach ($models as $model) {
            $model->revenueShare = $totalRevenue != 0 ? number_format(($model->revenue / $totalRevenue * 100), 2) : 0;
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function actionTop()
    {
        $model = new StatisticCustomer();
        $model->load(\Yii::$app->request->get(), '');

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'average_revenue',
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
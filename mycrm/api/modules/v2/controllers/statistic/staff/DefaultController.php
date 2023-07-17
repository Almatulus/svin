<?php
namespace api\modules\v2\controllers\statistic\staff;

use api\modules\v2\controllers\BaseController;
use core\forms\customer\statistic\StatisticStaff;
use core\forms\customer\StatisticStaffForm;
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
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $model = new StatisticStaffForm();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $dataProvider->setModels(array_map(function (StatisticStaff $staff) use ($model) {
            $staff->setFormModel($model);
            return $staff;
        }, $dataProvider->getModels()));
        return $dataProvider;
    }

    /**
     * @return array
     */
    public function actionTop()
    {
        $model = new StatisticStaffForm();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $dataProvider->setModels(array_map(function (StatisticStaff $staff) use ($model) {
            $staff->setFormModel($model);
            return $staff;
        }, $dataProvider->getModels()));
        return $model->getTop($dataProvider->getModels());
    }
}
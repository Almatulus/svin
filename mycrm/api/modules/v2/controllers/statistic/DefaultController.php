<?php
/**
 * Created by PhpStorm.
 * User: Tumar
 * Date: 30.01.2018
 * Time: 15:07
 */

namespace api\modules\v2\controllers\statistic;

use api\modules\v2\controllers\BaseController;
use core\forms\customer\StatisticForm;


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
     * @return StatisticForm
     */
    public function actionIndex()
    {
        $form = new StatisticForm();
        $form->scenario = StatisticForm::SCENARIO_GENERAL;
        $form->load(\Yii::$app->request->get());

        return $form;
    }
}
<?php

namespace api\modules\v2\controllers\webcall;


use api\modules\v2\controllers\BaseController;
use core\forms\webcall\WebCallForm;
use core\models\webcall\WebCall;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

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
    public function actionStatistics()
    {
        $webcall = $this->findModel(\Yii::$app->user->identity->company_id);

        $current_date = new \DateTime();
        $model = new WebCallForm($webcall);
        $previousWeekModel = new WebCallForm($webcall);

        $model->to_date = $current_date->format("Y-m-d");
        $model->from_date = $current_date->modify("-7 days")->format("Y-m-d");

        $model->load(\Yii::$app->request->get(), '');

        $end_date = (new \DateTime($model->from_date));
        $previousWeekModel->to_date = $end_date->format("Y-m-d");
        $previousWeekModel->from_date = $end_date->modify("-7 days")->format("Y-m-d");

        $model->getCallsList();
        $previousWeekModel->getCallsList();

        return [
            'now'      => ArrayHelper::toArray($model, $model->fields()),
            'previous' => ArrayHelper::toArray($previousWeekModel, $previousWeekModel->fields())
        ];
    }

    /**
     * Returns model
     * @return WebCall
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = WebCall::findOne(['company_id' => $id, 'enabled' => true]);
        if ($model == null) {
            throw new NotFoundHttpException('No web call is set up');
        }
        return $model;
    }

}
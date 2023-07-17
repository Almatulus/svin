<?php

namespace api\modules\v1\controllers;

use core\models\StaffReview;
use api\modules\v1\components\ApiController;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class StaffReviewController extends ApiController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                'tokenParam' => 'token',
            ]
        ]);
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $staff_id = \Yii::$app->request->getBodyParam('staff', null);
        $value = \Yii::$app->request->getBodyParam('value', 0);
        $comment = \Yii::$app->request->getBodyParam('comment', null);

        if ($staff_id !== null) {
            $model = StaffReview::find()
                ->where(["customer_id" => \Yii::$app->user->id, "staff_id" => $staff_id])->one();

            if ($model == null) {
                $model = new StaffReview();
                $model->staff_id = $staff_id;
                $model->customer_id = \Yii::$app->user->id;
            }
            $model->status = StaffReview::STATUS_ENABLED;
            $model->value = $value;
            $model->comment = $comment;

            try {
                if ($model->validate() && $model->save()) {
                    return ['status' => 200, 'message' => 'Review created'];
                } else {
                    throw new \Exception('Error while creating review');
                }
            } catch (\Exception $e) {
                return ['status' => 500, 'message' => Json::encode($e->getMessage())];
            }
        } else {
            return ['status' => 404, 'message' => 'Staff not set'];
        }
    }
}
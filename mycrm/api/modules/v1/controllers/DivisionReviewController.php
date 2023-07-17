<?php

namespace api\modules\v1\controllers;

use core\models\division\DivisionReview;
use api\modules\v1\components\ApiController;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;

class DivisionReviewController extends ApiController
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
        $division_id = \Yii::$app->request->getBodyParam('division', null);
        $value = \Yii::$app->request->getBodyParam('value', 0);
        $comment = \Yii::$app->request->getBodyParam('comment', null);

        if ($division_id !== null) {
            $model = DivisionReview::find()
                ->where(["customer_id" => \Yii::$app->user->id, "division_id" => $division_id])->one();

            if ($model == null) {
                $model = new DivisionReview();
                $model->division_id = $division_id;
                $model->customer_id = \Yii::$app->user->id;
                $model->status = DivisionReview::STATUS_ENABLED;
            }
            $model->value = $value;
            $model->comment = $comment;

            if ($model->save()) {
                return ['status' => 200, 'message' => 'Review created'];
            } else {
                return ['status' => 500, 'message' => 'Error while creating review'];
            }
        } else {
            return ['status' => 404, 'message' => 'Division not set'];
        }
    }
}
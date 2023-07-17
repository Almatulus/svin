<?php

namespace api\modules\v1\controllers;

use core\models\Image;
use core\models\Staff;
use core\models\division\DivisionService;
use api\modules\v1\components\ApiController;
use yii\helpers\Url;

class StaffController extends ApiController
{
    /**
     * Returns data list of staffs
     * @return array
     */
    public function actionIndex()
    {
        $query = Staff::find()->with(['companyPosition'])->limit(20);

        // Filter by division
        $division_id = \Yii::$app->request->getBodyParam('division', null);
        if ($division_id !== null) {
            $query->joinWith(['divisions']);
            $query->andWhere(['{{%divisions}}.id' => $division_id]);
        }

        // Filter by division
        $division_service_id = \Yii::$app->request->getBodyParam('service', null);
        if ($division_service_id !== null) {
            $query->joinWith("divisionServices");
            $query->andWhere(['{{%division_services}}.id' => $division_service_id]);
        }

        // Filter by division
        $staff_id = \Yii::$app->request->getBodyParam('id', null);
        if ($staff_id !== null) {
            $query->andWhere(['crm_staffs.id' => $staff_id]);
        }

        $models = $query->enabled()->timetableVisible()->all();

        $result = [];
        foreach ($models as $model) {
            /** @var Staff $model */
            $reviews = [];
            $reviews_model = $model->staffReviews;
            foreach ($reviews_model as $review) {
                $reviews[] = [
                    'name' => $review->customer->name,
                    'image' => $review->customer->getAvatarImageUrl(),
                    'value' => $review->value,
                    'comment' => $review->comment,
                    'datetime' => $review->created_time,
                ];
            }

            $services = [];
            /* @var DivisionService[] $service */
            $division_services = $model->divisionServices;
            foreach ($division_services as $division_service) {
                $services[] = [
                    'id' => $division_service->id,
                    'name' => $division_service->service_name,
                    'price' => $division_service->price,
                ];
            }

            $result[] = [
                'id' => $model->id,
                'name' => $model->name,
                'rating' => $model->getReviewValue(),
                'surname' => $model->surname,
                'image' => $model->getAvatarImageUrl(),
                'description' => $model->description,
                'position_id' => $model->companyPosition ? $model->companyPosition->id : null,
                'position_name' => $model->companyPosition ? $model->companyPosition->name : null,
                'reviews' => $reviews,
                'services' => $services,
            ];
        }

        return $result;
    }
}

<?php

namespace api\modules\v1\controllers;

use api\modules\v1\components\ApiController;
use core\models\division\DivisionService;
use core\models\Service;
use core\models\ServiceCategory;
use yii\web\NotFoundHttpException;

class ServiceController extends ApiController
{
    public function actionIndex()
    {
        $query = ServiceCategory::find()->orderBy("id");

        // Filter by category
        $categories = \Yii::$app->request->getBodyParam('category', null);
        if ($categories !== null) {
            $query->andWhere(['id' => $categories]);
        }
        $models = $query->all();

        $result = [];
        foreach ($models as $model) {
            $data = [];
            $categories = $model->serviceCategories;
            foreach ($categories as $category) {
                /** @var ServiceCategory $category */
                $data[] = [
                    'id'             => $category->id,
                    'name'           => $category->name,
                    'division_count' => $category->getDivisionsCount(),
                ];
            }

            $result[] = [
                "category_id" => $model->id,
                "name"        => $model->name,
                "services"    => $data,
            ];
        }

        return $result;
    }

    /**
     * Returns division service information
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDivisionService($id) {
        $divisionService = DivisionService::find()->where(['id' => $id])->asArray()->one();

        if ($divisionService == null) {
            return ['error' => 404, 'message' => 'Division with this service not found'];
            //throw new NotFoundHttpException('No division service found');
        }

        return $divisionService;
    }
}

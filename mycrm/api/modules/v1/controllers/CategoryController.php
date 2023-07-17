<?php

namespace api\modules\v1\controllers;

use api\modules\v1\components\ApiController;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\ServiceCategory;

class CategoryController extends ApiController
{
    public function actionIndex()
    {
        return ServiceCategory::find()
            ->select(['id', 'name'])
            ->orderBy(['order' => SORT_ASC])
            ->where(['parent_category_id' => null, 'type' => ServiceCategory::TYPE_CATEGORY_STATIC])
            ->all();
    }

    public function actionView($id)
    {
        return ServiceCategory::findOne($id);
    }

    public function actionServices($division_id)
    {
        $division = Division::findOne($division_id);
        $allServices = [];
        if ($division) {
            $data = ServiceCategory::getCompanyCategories($division->company_id);

            foreach ($data as $key => $category) {
                $services = DivisionService::find()
                    ->joinWith(['categories', 'divisions'], false)
                    ->where([
                        '{{%divisions}}.id'             => $division_id,
                        '{{%division_services}}.status' => ServiceCategory::STATUS_ENABLED,
                        'publish'                       => true,
                    ])->andWhere([
                        'OR',
                        ['{{%service_categories}}.id' => $category['id']],
                        ['{{%service_categories}}.parent_category_id' => $category['id']],
                    ])
                    ->orderBy('service_name ASC')
                    ->asArray()
                    ->all();

                foreach ($services as &$service) {
                    $service['division_id'] = $division_id;
                }

                if (!empty($services)) {
                    $allServices[] = [
                        'id'       => $category['id'],
                        'name'     => $category['name'],
                        'services' => $services
                    ];
                }
            }
        }

        return $allServices;
    }
}
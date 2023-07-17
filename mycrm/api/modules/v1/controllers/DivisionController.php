<?php

namespace api\modules\v1\controllers;

use api\modules\v1\components\ApiController;
use core\models\company\Company;
use core\models\customer\CustomerFavourite;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\query\DivisionQuery;
use core\models\ServiceCategory;
use yii\data\ActiveDataProvider;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;

class DivisionController extends ApiController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class'      => QueryParamAuth::className(),
                'except'     => ['index', 'view'],
                'tokenParam' => 'token',
            ],
        ]);
    }

    /**
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $query = Division::find()->joinWith('company');
        $query->joinWith(['divisionServices.categories', 'divisionPayments']);

        $query = self::filter($query);

        $models = $query->all();

        $is_open = \Yii::$app->request->getBodyParam('is_open', null);

        $category_id = \Yii::$app->request->getBodyParam('category', null);
        $category = ServiceCategory::findOne(['id' => $category_id]);

        $divisions = [];
        foreach ($models as $model) {
            /* @var Division $model */
            // Filter by open
            if ($is_open !== null && $is_open != $model->isOpen()) {
                continue;
            }

            $divisions[] = $model;
            $result['divisions'][] = $model->getInformation($category);
        }

        $result['max_price'] = self::price();
        $result['min_price'] = self::price(false);
        $result['count'] = count($divisions);

        return $result;
    }

    public function actionInfo()
    {
        $query = Division::find()->joinWith('company');
        $query->joinWith(['divisionServices.services', 'divisionPayments']);

        $query = self::filter($query);

        $models = $query->publish(Company::PUBLISH_TRUE)->all();

        $is_open = \Yii::$app->request->getBodyParam('is_open', null);
        $service_id = \Yii::$app->request->getBodyParam('service', null);
        $service = Service::findOne(['id' => $service_id]);

        $divisions = [];
        foreach ($models as $model) {
            /* @var Division $model */
            // Filter by open
            if ($is_open !== null && $is_open != $model->isOpen()) {
                continue;
            }

            $divisions[] = $model;
            $result['divisions'][] = $model->getInformation($service);
        }

        $result['max_price'] = self::price();
        $result['min_price'] = self::price(false);
        $result['count'] = count($divisions);

        return $result;
    }

    public function actionView($id)
    {
        if (($model = Division::find()
                ->where(['crm_divisions.id' => $id])
                ->enabled()
                ->publish(Company::PUBLISH_TRUE)
                ->one()) !== null
        ) {
            /* @var Division $model */
            return $model->getInformation();
        } else {
            return [];
        }
    }

    public function actionFavourite()
    {
        $division_id = \Yii::$app->request->getBodyParam('division', null);
        if ($division_id !== null) {
            /* @var Division $division */
            $division = Division::findOne($division_id);
            if (!$division->isFavourite(\Yii::$app->user->identity)) {
                $model = new CustomerFavourite();
                $model->customer_id = \Yii::$app->user->id;
                $model->division_id = $division->id;
                if ($model->save()) {
                    return ['status' => 200, 'favourite' => true, 'message' => 'Division set favourite'];
                } else {
                    return ['status' => 500, 'message' => 'Error while saving'];
                }
            } else {
                CustomerFavourite::deleteAll([
                    "customer_id" => \Yii::$app->user->id, "division_id" => $division_id
                ]);
                return ['status' => 200, 'favourite' => false, 'message' => 'Division set not favourite'];
            }
        } else {
            $models = CustomerFavourite::findAll(["customer_id" => \Yii::$app->user->id]);
            $result = [];
            foreach ($models as $model) {
                /* @var CustomerFavourite $model */
                $result[] = $model->division->getInformation();
            }
            return $result;
        }
    }

    /**
     * Returns divisions service max price
     * @param boolean $max_price
     * @return integer
     */
    private static function price($max_price = true)
    {
        $query = DivisionService::find()
            ->deleted(false)
            ->joinWith(['categories', 'divisions.company'])
            ->andWhere(['crm_divisions.status' => Division::STATUS_ENABLED])
            ->andWhere(['crm_companies.publish' => Company::PUBLISH_TRUE]);

        // Filter by city
        if (($city = \Yii::$app->request->getBodyParam('city', null)) !== null) {
            $query->andWhere(["crm_divisions.city_id" => $city]);
        }

        // Filter by category
        if (($category = \Yii::$app->request->getBodyParam('category', null)) !== null) {
            $query->andWhere(['crm_services.category_id' => $category]);
        }

        // Filter by searching text
        if (($search = \Yii::$app->request->getBodyParam('search', null)) !== null) {
            $query->andFilterWhere(['like', 'lower(crm_divisions.name)', mb_strtolower($search)]);
        }

        if ($max_price)
            return $query->max('price');
        else
            return $query->min('price');
    }

    /**
     * @param DivisionQuery $query
     * @return DivisionQuery $query
     */
    private static function filter(DivisionQuery $query)
    {
        // Filter by distance
        if (($latitude = \Yii::$app->request->getBodyParam('latitude', null)) !== null &&
            ($longitude = \Yii::$app->request->getBodyParam('longitude', null)) !== null
        ) {
            $query->select("crm_divisions.id, crm_divisions.name, crm_divisions.url, " .
                " crm_divisions.address, crm_divisions.company_id, crm_divisions.city_id, " .
                " crm_divisions.status, crm_divisions.rating, crm_divisions.latitude, " .
                " crm_divisions.longitude, crm_divisions.working_start, crm_divisions.working_finish, " .
                pg_escape_string("((ACOS(SIN({$latitude} * PI() / 180) * SIN(crm_divisions.latitude * PI() / 180) + "
                    . " COS({$latitude} * PI() / 180) * COS(crm_divisions.latitude * PI() / 180) * "
                    . " COS(({$longitude} - crm_divisions.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 1.6) as distance"));
        }

        // Filter by city
        if (($city = \Yii::$app->request->getBodyParam('city', null)) !== null) {
            $query->andWhere(["city_id" => $city]);
        }

        // Filter by card payment
        if (($has_payment = \Yii::$app->request->getBodyParam('has_payment', null)) !== null) {
            $query->andWhere(["crm_division_payments.payment_id" => intval($has_payment)]);
        }

        // Filter by price
        if (($price_start = \Yii::$app->request->getBodyParam('price_start', null)) !== null) {
            $query->andWhere("crm_division_services.price >= :price_start", [":price_start" => $price_start]);
        }
        if (($price_finish = \Yii::$app->request->getBodyParam('price_finish', null)) !== null) {
            $query->andWhere("crm_division_services.price <= :price_finish", [":price_finish" => $price_finish]);
        }

        // Filter by category
        if (($category = \Yii::$app->request->getBodyParam('category', null)) !== null) {
            $query->andWhere(['crm_services.category_id' => $category]);
        } else
            // Filter by service
            if (($service_id = \Yii::$app->request->getBodyParam('service', null)) !== null) {
                $query->andWhere(['crm_services.id' => $service_id]);
            }

        // Filter by searching text
        if (($search = \Yii::$app->request->getBodyParam('search', null)) !== null) {
            $query->andFilterWhere(['like', 'lower(crm_divisions.name)', mb_strtolower($search)]);
        }

        // Filter by company id
        if (($company_id = \Yii::$app->request->getBodyParam('company', null)) !== null) {
            $query->andWhere(['crm_divisions.company_id' => $company_id]);
        }

        // Filter by widget prefix
        if (($prefix = \Yii::$app->request->getBodyParam('prefix', null)) !== null) {
            $query->andWhere(['{{%companies}}.widget_prefix' => $prefix]);
        }

        /**
         * Sort
         * 1 => Relevance
         * 2 => Price up
         * 3 => Price down
         * 4 => Distance up
         */
        $sorts = \Yii::$app->request->getBodyParam('sort', []);
        $orders = [];
        if (!is_array($sorts)) {
            $sorter = $sorts;
            unset($sorts);
            $sorts[] = $sorter;
        }

        foreach ($sorts as $sort) {
            switch ($sort) {
                case "relevance":
                    $orders['crm_divisions.name'] = SORT_ASC;
                    break;
                case "-relevance":
                    $orders['crm_divisions.name'] = SORT_DESC;
                    break;
                case "price":
                    $orders['crm_division_services.price'] = SORT_ASC;
                    break;
                case "-price":
                    $orders['crm_division_services.price'] = SORT_DESC;
                    break;
                case "-review":
                    $orders['crm_divisions.rating'] = SORT_DESC;
                    break;
                case "review":
                    $orders['crm_divisions.rating'] = SORT_ASC;
                    break;
                case "distance":
                    $orders['distance'] = SORT_ASC;
                    break;
                case "-distance":
                    $orders['distance'] = SORT_DESC;
                    break;
                default:
                    $orders['crm_divisions.rating'] = SORT_DESC;
                    break;
            }
        }

        if (empty($orders)) {
            $orders['crm_divisions.rating'] = SORT_DESC;
        }
        $query->orderBy($orders);

        $query->andWhere(['crm_divisions.status' => Division::STATUS_ENABLED]);
        $query->publish(Company::PUBLISH_TRUE);

        return $query;
    }
}

<?php

namespace core\repositories\finance;

use core\models\finance\CompanyCashflowService;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class CompanyCashflowServiceRepository extends BaseRepository
{
    /**
     * @param integer $id
     * @return CompanyCashflowService
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CompanyCashflowService::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $order_service_id
     * @return CompanyCashflowService
     */
    public function findByOrderService($order_service_id)
    {
        /* @var CompanyCashflowService $model */
        $model = CompanyCashflowService::findOne(['order_service_id' => $order_service_id]);
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $order_id
     * @return CompanyCashflowService[]
     */
    public function findAllByOrder($order_id)
    {
        return CompanyCashflowService::find()
            ->joinWith('orderService')
            ->where(['{{%order_services}}.order_id' => $order_id])
            ->all();
    }
}
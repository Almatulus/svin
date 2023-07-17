<?php

namespace core\repositories\order;

use core\models\order\OrderService;
use core\models\order\query\OrderQuery;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class OrderServiceRepository extends BaseRepository
{
    /**
     * @param $id
     * @return OrderService
     */
    public function find($id)
    {
        if (!$model = OrderService::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @param $division_service_id
     * @return OrderService
     */
    public function findByOrderAndDivisionService($order_id, $division_service_id)
    {
        /* @var OrderService $model */
        $model = OrderService::find()->where(['order_id' => $order_id, 'division_service_id' => $division_service_id])->one();
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @return OrderService[]
     */
    public function findAllByOrder($order_id)
    {
        return OrderService::find()
            ->where([
                'order_id' => $order_id,
                'deleted_time' => null
            ])
            ->all();
    }

    /**
     * @param int $staff_id
     * @param int $division_id
     * @param \DateTime $start
     * @param \DateTime $end
     * @param bool $eagerLoading
     * @param bool $as_array
     * @return OrderService[]
     */
    public function findByStaffAndRange(
        int $staff_id,
        int $division_id,
        \DateTime $start,
        \DateTime $end,
        $eagerLoading = true,
        $as_array = false
    ) {
        return OrderService::find()
            ->joinWith([
                'order' => function (OrderQuery $query) use ($staff_id, $division_id, $start, $end) {
                    $query->finished()->staff($staff_id)->division($division_id)->startFrom($start)->to($end);
                },
                'divisionService',
                'order.cashflows'
            ], $eagerLoading)
            ->andWhere(['{{%order_services}}.deleted_time' => null])
            ->orderBy('{{%orders}}.datetime ASC')
            ->asArray($as_array)
            ->all();
    }
}
<?php

namespace core\repositories\order;

use core\helpers\order\OrderConstants;
use core\models\File;
use core\models\order\Order;
use core\repositories\exceptions\NotFoundException;

class OrderRepository
{
    /**
     * @param $id
     *
     * @return Order
     */
    public function find($id)
    {
        if ( ! $model = Order::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param $file_id
     *
     * @return Order
     */
    public function findByFileId($file_id)
    {
        $model = Order::find()
            ->joinWith('files', false)
            ->where(['file_id' => $file_id])
            ->one();
        if ( ! $model) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param integer $company_customer_id
     *
     * @return Order[]
     */
    public function findAllFinishedWithDebtByCompanyCustomer(
        $company_customer_id
    ) {
        return Order::find()
            ->where([
                'company_customer_id' => $company_customer_id,
                'status'              => OrderConstants::STATUS_FINISHED,
            ])
            ->andWhere(['<', 'payment_difference', 0])
            ->orderBy('datetime')
            ->all();
    }

    /**
     * @return Order[]
     */
    public function findAllUnNotified()
    {
        return Order::find()->where([
            '{{%orders}}.notify_status' => OrderConstants::NOTIFY_TRUE,
            '{{%orders}}.status'        => OrderConstants::STATUS_ENABLED,
        ])->andWhere('{{%orders}}.datetime > :now', [
            ':now' => date('Y-m-d H:i:s'),
        ])->joinWith(['companyCustomer', 'companyCustomer.customer'])->all();
    }

    /**
     * @param Order $model
     */
    public function save(Order $model)
    {
        if ($model->save() === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param Order $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(Order $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param Order $model
     * @param File  $file
     *
     * @throws \yii\db\Exception
     */
    public function linkFile(Order $model, File $file)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->insert("{{%order_files}}", [
            'order_id' => $model->id,
            'file_id'  => $file->id
        ])->execute();
    }

    /**
     * @param int $file_id
     *
     * @throws \yii\db\Exception
     */
    public function unlinkFileByFileId(int $file_id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->delete("{{%order_files}}", [
            'file_id' => $file_id
        ])->execute();
    }

    /**
     * @param array $order_service_ids
     * @param bool $value
     * @return int
     */
    public function setPaidByOrderServiceIds(array $order_service_ids, $value = true)
    {
        $orderIds = Order::find()->joinWith('orderServices', false)->andWhere([
            '{{%order_services}}.id' => $order_service_ids
        ])->select('{{%orders}}.id')->column();

        return Order::updateAll(['is_paid' => $value], ['id' => $orderIds]);
    }

    /**
     * @param int       $staff_id
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function setStaffUnpaidForPeriod(
        int $staff_id,
        \DateTime $from,
        \DateTime $to
    ) {
        $orderIds = Order::find()
            ->where([
                'staff_id' => $staff_id,
                'is_paid'  => true
            ])
            ->startFrom($from)
            ->to($to)
            ->select('{{%orders}}.id')
            ->column();

        return Order::updateAll(['is_paid' => false], ['id' => $orderIds]);
    }

    /**
     * @param string $start
     * @param string $end
     * @param int $division_id
     * @param int $staff_id
     * @return bool
     */
    public function findExistingOrderOfStaff(string $start, string $end, int $division_id, int $staff_id)
    {
        return Order::find()->company()
            ->division($division_id)
            ->staff($staff_id)
            ->andWhere([
                'OR',
                "datetime <= :start AND :start <= datetime + duration * INTERVAL '1 MINUTE'",
                "datetime <= :end AND :end <= datetime + duration * INTERVAL '1 MINUTE'"
            ])
            ->params([':end' => $end, ':start' => $start])
            ->status([OrderConstants::STATUS_ENABLED, OrderConstants::STATUS_FINISHED])
            ->exists();
    }
}

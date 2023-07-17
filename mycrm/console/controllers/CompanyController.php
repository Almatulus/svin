<?php

namespace console\controllers;

use core\models\ApiHistory;
use core\models\company\Company;
use core\models\company\Notification;
use core\models\customer\CustomerRequest;
use core\models\HistoryEntity;
use core\models\order\Order;
use core\models\user\User;
use Yii;
use yii\console\Controller;

class CompanyController extends Controller
{
    /**
     * Send sms notification if company has less than minimum balance
     */
    public function actionCheckBalance()
    {
        /** @var Company[] $companies */
        $companies = Company::find()
            ->enabled()
            ->where([
                '<',
                'balance',
                Notification::MIN_BALANCE,
            ])
            ->all();

        foreach ($companies as $company) {

            $notified = Notification::find()->where([
                'company_id' => $company->id,
                'type'       => Notification::TYPE_MIN_BALANCE,
            ])->exists();

            if ( ! $notified) {

                $notification = new Notification([
                    'company_id' => $company->id,
                    'type'       => Notification::TYPE_MIN_BALANCE,
                ]);
                $notification->save(false);

                $message = Yii::t('app',
                    "Your balance is less than {amount} {currency}.", [
                        'amount'   => Notification::MIN_BALANCE,
                        'currency' => 'тг',
                    ]);

                CustomerRequest::sendNotAssignedSMS(
                    $company->phone,
                    $message,
                    $company->id
                );
            }
        }
    }

    /**
     * @param int $company_id
     */
    public function actionFindMissingOrders(int $company_id = null)
    {
        $user_ids = User::find()->select('id')->enabled()->andFilterWhere(['{{%users}}.company_id' => $company_id])->column();

        $logs = ApiHistory::find()->where(['user_id' => $user_ids])
            ->andWhere(['like', 'url', '/order?'])
            ->andWhere(['request_method' => 'POST'])
            ->andWhere(['response_status_code' => 200]);

        foreach ($logs->each(200) as $log) {
            /** @var ApiHistory $log */
            $request_body = json_decode($log->request_body, true);
            $datetime = $request_body['datetime'];

            $order = Order::find()->andWhere(['datetime' => $datetime])
                ->andWhere(['created_user_id' => $log->user_id])
                ->one();

            if (!$order) {
                // check if order was created. Fetch id because datetime could be updated and find order by this id
                // if not found then order was created and deleted
                $row_id = HistoryEntity::find()->select('row_id')
                    ->andWhere(['initiator' => $log->user_id])
                    ->andWhere(['event' => 'afterInsert'])
                    ->andWhere(['class' => Order::class])
                    ->andWhere(['=', 'created_time', $log->created_time])
//                    ->andWhere(['<=', 'created_time', (new \DateTime($log->created_time))->modify("+1 second")->format("Y-m-d H:i:s")])
                    ->scalar();

                if ($row_id) {
                    $order_still_exists = Order::findOne($row_id);
                    if (!$order_still_exists) {
                        echo "Order with datetime {$datetime} not found. Log ID = {$log->id}" . PHP_EOL;
                    }
                }
            }
        }
    }

}
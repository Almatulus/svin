<?php

namespace core\helpers\order;

use common\components\events\CustomerRequestEventHandler;
use common\components\events\NotificationEventHandler;
use common\components\events\PushNotificationEventHandler;
use common\components\Notification;
use core\models\order\Order;
use core\models\user\User;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class OrderNotifier extends Component
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(CustomerRequestEventHandler::EVENT_ORDER_UPDATED, ['core\models\customer\CustomerRequest', 'sendOrderTemplateRequest']);
        $this->on(NotificationEventHandler::EVENT_ORDER_CREATED, ['common\components\Notification', 'sendNotification']);
        $this->on(PushNotificationEventHandler::EVENT_ORDER_TIME_CHANGED, ['common\components\PushNotification', 'sendNotification']);
        $this->on(PushNotificationEventHandler::EVENT_ORDER_STATUS_CONFIRMED, ['common\components\PushNotification', 'sendNotification']);
    }

    /**
     * @param Order $order
     * @param string $phone
     * @param integer $template
     */
    public function sendSMSNotification($order, $phone, $template)
    {
        if (YII_ENV_TEST) {
            return;
        }

        if ($order->companyCustomer->company->hasEnoughBalance(Yii::$app->params['sms_cost'])) {
            $this->trigger(CustomerRequestEventHandler::EVENT_ORDER_UPDATED,
                new CustomerRequestEventHandler([
                    'order' => $order,
                    'receiver' => $phone,
                    'template' => $template,
                ])
            );
        } else {
            if (isset(Yii::$app->user->identity)) {
                $user_id = Yii::$app->user->identity->id;
                $message = "У вас недостаточно средств, чтобы отправить SMS.<br>Пополните пожалуйста баланс в разделе Настройки.";
                exec("(php " . Yii::$app->basePath . "/yii notification/send-jgrowl {$user_id} {$message} flash_alert) &");
            }
        }
    }

    /**
     * @param Order $order
     * @param bool  $insert
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function sendWebNotification(Order $order, bool $insert)
    {
        $users = ArrayHelper::getColumn($order->division->users, "id");

        $admins = ArrayHelper::getColumn(User::find()
            ->company($order->division->company_id)
            ->joinWith('staff', false)
            ->enabled()
            ->andWhere(['{{%staffs}}.id' => null])
            ->all(), 'id');

        $receivers = array_merge($users, $admins);

        if ($order->created_user_id) {
            $receivers = array_diff($receivers, [$order->created_user_id]);
        }

        $receivers = array_unique(array_filter($receivers));

        $customer = $order->companyCustomer->customer;
        $this->trigger(NotificationEventHandler::EVENT_ORDER_CREATED,
            new NotificationEventHandler([
                'message' => $insert ? Yii::t('app',
                    'New order notification message {name} {phone} {service} {staff} {datetime} {link}', [
                    'name'     => $customer->name,
                    'phone'    => $customer->phone,
                    'service'  => $order->getServicesTitle(),
                    'staff'    => $order->staff->getFullName(),
                    'datetime' => Yii::$app->formatter->asDatetime($order->datetime),
                    'link'     => Html::a(Yii::t('app', 'Show all orders'), ['/order/order/index'])
                    ]) : "",
                'type'    => Notification::TYPE_NOTIFICATION,
                'users'   => $receivers,
            ])
        );
    }

    /**
     * @param $eventName
     * @param Order $order
     * @param string $message
     * @param mixed $callback
     */
    public function sendMobilePush($eventName, $order, $message, $callback) {
        $this->trigger($eventName,
            new PushNotificationEventHandler([
                'title' => "Запись в '" . $order->division->name . "'",
                'message' => $message,
                'customer' => $order->companyCustomer->customer,
                'division_id' => $order->division_id,
                'callback' => $callback,
            ])
        );
    }

}
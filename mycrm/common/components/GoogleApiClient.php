<?php

namespace common\components;

use core\helpers\order\OrderConstants;
use core\models\order\Order;
use Yii;
use yii\base\Component;

/*
 * Google Api Wrapper
 */

class GoogleApiClient extends Component
{

    public $client;
    public $applicationName = 'MyCRM';
    public $credentialsPath = 'credentials.json';
    public $clientSecretPath = 'client_secrets.json';
    public $redirectUri = 'https://crm.mycrm.kz/oauth2/index/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getClient();
    }

    /**
     * @return \Google_Client
     */
    public function getClient()
    {
        if ( ! $this->client) {
            $this->client = new \Google_Client();
            $this->client->setApplicationName($this->applicationName);
            $this->client->setAuthConfig(
                Yii::getAlias('@common') . "/config/" . $this->clientSecretPath
            );
            $this->client->setScopes(\Google_Service_Calendar::CALENDAR);
            $this->client->setAccessType('offline');
            $this->client->setApprovalPrompt('force');
            $this->client->setIncludeGrantedScopes(true);
            $this->client->setRedirectUri($this->redirectUri);
        }

        return $this->client;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getAuthUrl($data)
    {
        $this->client->setState($data);

        return $this->client->createAuthUrl();
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function addEvent($order)
    {
        $refreshToken = $order->staff->user->google_refresh_token;
        if (isset($order->staff->user) && $refreshToken) {
            if ($this->client->isAccessTokenExpired()) {
                $this->client->refreshToken($refreshToken);
            }

            $service = new \Google_Service_Calendar($this->client);

            try {
                $event = $service->events->get(
                    "primary",
                    $order->google_event_id
                );
                $event = $this->createEventFromOrder($order, $event);
                $service->events->update('primary', $event->getId(), $event);

                return true;
            } catch (\Exception $e) {
                $event      = $this->createEventFromOrder($order);
                $calendarId = 'primary';
                $event      = $service->events->insert($calendarId, $event);
                $order->updateAttributes(['google_event_id' => $event->id]);

                return true;
            }

        }

        return false;
    }

    /**
     * @param      $order
     * @param null $event
     *
     * @return \Google_Service_Calendar_Event|null
     */
    public function createEventFromOrder($order, $event = null)
    {
        if ( ! $event) {
            $event = new \Google_Service_Calendar_Event();
        }

        $dateFormat = "Y-m-d\TH:i:s";
        $startDate  = new \DateTime($order->datetime);
        $endDate    = (new \DateTime($order->datetime))
            ->modify("+" . ($order->duration) . " minutes");
        $start      = new \Google_Service_Calendar_EventDateTime([
            'dateTime' => date(
                $dateFormat,
                strtotime($startDate->format("Y-m-d H:i:s"))
            ),
            'timeZone' => 'Asia/Almaty',
        ]);
        $end        = new \Google_Service_Calendar_EventDateTime([
            'dateTime' => date(
                $dateFormat,
                strtotime($endDate->format("Y-m-d H:i:s"))
            ),
            'timeZone' => 'Asia/Almaty',
        ]);

        $event->setSummary($order->servicesTitle . " - "
                           . $order->companyCustomer->customer->name);
        $event->setDescription($order->note);
        $event->setStart($start);
        $event->setEnd($end);

        return $event;
    }

    /**
     * @param $staff_id
     */
    public function import($staff_id)
    {
        $orders_query = Order::find()
                             ->startFrom(new \DateTime())
                             ->status(OrderConstants::STATUS_ENABLED)
                             ->staff($staff_id)
                             ->orderBy('datetime DESC');

        foreach ($orders_query->each() as $key => $order) {
            $this->addEvent($order);
        }
    }
}
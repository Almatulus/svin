<?php

namespace core\services;

use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;

class PushService
{
    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var Client
     */
    private $_client;

    /**
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     */
    public function sendTemplate($tokens = [], string $title, string $body, array $data = [])
    {
        $message = $this->createMessage($tokens);
        $notification = $this->createNotification($body, $title);
        $notification->setContentAvailable(true);
        $notification->setSound('default');
        $message->setNotification($notification);
        $message->setPriority("high");
        $message->setJsonKey('content-available', true);
        $message->setJsonKey('apns', ['headers' => ['apns-priority' => 10]]);
        $message->setData($data);
        $this->send($message);
    }

    /**
     * @param array $tokens
     * @return Message
     */
    public function createMessage($tokens = [])
    {
        $message = new Message();

        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        foreach ($tokens as $token) {
            $message->addRecipient($this->createDevice($token));
        }

        return $message;
    }

    /**
     * @param string $token
     * @return Device
     */
    public function createDevice(string $token)
    {
        return new Device($token);
    }

    /**
     * @param string $message
     * @param string $title
     * @return Notification
     */
    public function createNotification(string $message, string $title)
    {
        return new Notification($title, $message);
    }

    /**
     * @param Message $message
     */
    public function send(Message $message)
    {
        var_dump($this->getClient()->send($message));
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if (!$this->_client) {
            $this->_client = $this->createClient();
        }
        return $this->_client;
    }

    /**
     * @return Client
     */
    private function createClient()
    {
        $client = new Client();
        $client->setApiKey($this->apiKey);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());
        return $client;
    }
}
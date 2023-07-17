<?php
namespace core\jobs\customer;
use core\services\customer\CustomerRequestService;
use core\repositories\customer\CustomerRequestRepository;
use Exception;
use Yii;
use yii\base\BaseObject;

class SendCustomerRequestWAJob extends BaseObject implements \yii\queue\JobInterface
{
    public $requestId;
    private $customerRequests;
    private $company;

    public function __construct(
        array $config = []
    ) {
        $this->customerRequests = Yii::$container->get('core\repositories\customer\CustomerRequestRepository');
        parent::__construct($config);
    }

    public function execute($queue)
    {
        $customerRequest = $this->customerRequests->find($this->requestId);
        if( $customerRequest ){
            $this->company = $customerRequest->company;
            $receiver = str_replace(" ", '', str_replace("+", '', $customerRequest->receiver_phone));
            $message = str_replace("'", '', $customerRequest->code);
            $this->send($receiver, $message);        
        }
    }

    /**
     * @param string $phone
     * @param string $message
     * @return mixed
     */
    private function send($phone, $message)
    {
        if (!($curl = curl_init())) {
            throw new \DomainException('CURL error');
        }

        $this->guardCredentials();

        $params = [
            "channelId" => "905734b0-44d0-4ffb-b5aa-7afb1d4ecea0",
            "chatType" => "whatsapp",
            "chatId" => $phone,
            "text" => $message,
        ];

        curl_setopt($curl, CURLOPT_URL, $this->company->chatapi_url . 'v2/send_message');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Basic ' . $this->company->chatapi_token));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    private function guardCredentials()
    {
        if (empty($this->company->chatapi_token)) {
            throw new \DomainException('chatapi_token not set');
        }

        if (empty($this->company->chatapi_url)) {
            throw new \DomainException('chatapi_url not set');
        }
    }

}
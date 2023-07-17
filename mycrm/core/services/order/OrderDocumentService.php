<?php

namespace core\services\order;

use common\components\DocGenerator;
use core\models\company\Company;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\repositories\BaseRepository;
use core\repositories\order\OrderRepository;
use core\services\TransactionManager;
use Yii;

class OrderDocumentService
{
    private $orderRepository;
    private $transactionManager;

    public function __construct(
        BaseRepository $baseRepository,
        OrderRepository $orderRepository,
        TransactionManager $transactionManager
    ) {
        $this->baseRepository     = $baseRepository;
        $this->orderRepository    = $orderRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param $order_id
     * @param $template_id
     * @param $user_id
     *
     * @return OrderDocument
     * @throws \Exception
     */
    public function add(int $order_id, int $template_id, int $user_id)
    {
        $order = $this->orderRepository->find($order_id);

        $document  = new OrderDocument([
            'date'        => date("Y-m-d H:i:s"),
            'order_id'    => $order->id,
            'template_id' => $template_id,
            'user_id'     => $user_id,
        ]);
        $file_path = $this->generateDocument(
            $template_id,
            $order,
            $document
        );

        $document->path = $this->uploadToS3($order, $file_path);

        $this->transactionManager->execute(function () use ($document) {
            $this->baseRepository->add($document);
        });

        return $document;
    }

    /**
     * Saves word document and returns document path
     *
     * @param $template_id
     * @param $order
     * @param $document
     *
     * @return string
     */
    public function generateDocument($template_id, $order, $document)
    {
        $searchPatterns = [
            'COMPANY_NAME',
            'COMPANY_ADDRESS',
            'COMPANY_CEO',
            'COMPANY_IIK',
            'COMPANY_BIK',
            'COMPANY_BIN',
            'CUSTOMER_ADDRESS',
            'CUSTOMER_FULLNAME',
            'CUSTOMER_ID_CARD_NUMBER',
            'CUSTOMER_IIN',
            'STAFF_FULLNAME',
            'DATE',
            'COMPANY_LICENSE_NAME',
            'COMPANY_LICENSE_ISSUED',
        ];

        /* @var Company $company */
        $company = $order->division->company;
        $companyCustomer = $order->companyCustomer;
        $customer        = $companyCustomer->customer;
        $values          = [
            $company->name,
            $company->address,
            $company->getCeoName(),
            $company->iik,
            $company->bik,
            $company->bin,
            $companyCustomer->address,
            $customer->fullName,
            $customer->id_card_number,
            $customer->iin,
            $order->staff->fullName,
            date("d.m.Y"),
            $company->license_number,
            $company->license_issued
        ];

        return DocGenerator::generateTemplate(
            $template_id,
            $searchPatterns,
            $values,
            $order->id,
            $document->date
        );
    }

    /**
     * @param Order  $order
     * @param string $localPath
     *
     * @return mixed
     */
    public function uploadToS3(Order $order, $localPath)
    {
        $extension = pathinfo($localPath)['extension'];
        $key = 'companies/'.$order->companyCustomer->company_id.'/orders/'
            .$order->id.'/documents/'.md5(time() . rand()) . '.' . $extension;

        $result = Yii::$app->get('s3')->upload($key, $localPath);

        return $result->get('ObjectURL');
    }
}

<?php

namespace core\services\order;

use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use core\models\company\Company;
use core\models\File;
use core\models\order\Order;
use core\repositories\company\CompanyRepository;
use core\repositories\order\OrderRepository;
use core\repositories\exceptions\NotFoundException;
use core\repositories\FileRepository;
use core\services\TransactionManager;
use Yii;

class OrderStorageService
{
    private $fileRepository;
    private $orderRepository;
    private $transactionManager;
    private $companies;

    public function __construct(
        FileRepository $fileRepository,
        OrderRepository $orderRepository,
        CompanyRepository $companies,
        TransactionManager $transactionManager)
    {
        $this->fileRepository = $fileRepository;
        $this->orderRepository = $orderRepository;
        $this->transactionManager = $transactionManager;
        $this->companies = $companies;
    }

    /**
     * @param integer $company_id
     * @param integer $order_id
     * @param string $realName
     * @param string $localPath
     *
     * @return File
     * @throws \Exception
     */
    public function upload($company_id, $order_id, $realName, $localPath)
    {
        $order = $this->orderRepository->find($order_id);
        $company = $this->companies->find($company_id);

        $this->guardOrderOwner($order, $company);

        $key = 'companies/' . $company_id . '/orders/' . $order_id . '/static/' . $realName;
        $this->guardFileUniqueness($key);

        try {
            $result = Yii::$app->get('s3')->upload($key, $localPath);

            $file = File::add($result->get('ObjectURL'), $realName);

            $this->transactionManager->execute(function() use ($order, $file) {
                $this->fileRepository->add($file);
                $this->orderRepository->linkFile($order, $file);
            });

            return $file;

        } catch(S3Exception $e) {
            throw new \DomainException($e->getMessage());
        } catch(AwsException $e) {
            throw new \DomainException($e->getAwsErrorCode());
        }
    }

    /**
     * @param $id
     *
     * @return void
     * @throws \Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function delete($id)
    {
        $file = $this->fileRepository->find($id);

        $this->guardAccess($file);

        try {
            $key = str_replace("https://s3.amazonaws.com/mycrmkzbucket/", "", $file->path);
            Yii::$app->get('s3')->delete($key);

            $this->transactionManager->execute(function() use ($file) {
                $this->orderRepository->unlinkFileByFileId($file->id);
                $this->fileRepository->delete($file);
            });
        } catch(S3Exception $e) {
            throw new \DomainException($e->getMessage());
        } catch(AwsException $e) {
            throw new \DomainException($e->getAwsErrorCode());
        }
    }

    /**
     * @param $key
     */
    public function guardFileUniqueness($key)
    {
        try {
            $this->fileRepository->findByPath("https://s3.amazonaws.com/mycrmkzbucket/" . $key);
            throw new \DomainException(Yii::t('app', 'File with such name already exists'));
        } catch (NotFoundException $e) {

        }
    }

    /**
     * @param Order   $order
     * @param Company $company
     *
     * @throws \yii\web\ForbiddenHttpException
     */
    public function guardOrderOwner(Order $order, Company $company)
    {
        if ($order->division->company_id !== $company->id) {
            throw new \yii\web\ForbiddenHttpException();
        }
    }

    /**
     * @param $file
     * @throws \yii\web\ForbiddenHttpException
     */
    public function guardAccess($file)
    {
        $order = $this->orderRepository->findByFileId($file->id);

        if ($order->division->company_id != Yii::$app->user->identity->company_id) {
            throw new \yii\web\ForbiddenHttpException();
        }
    }
}
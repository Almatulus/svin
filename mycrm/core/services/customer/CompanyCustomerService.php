<?php

namespace core\services\customer;

use common\components\excel\ExcelFileConfig;
use common\components\excel\ExcelRow;
use core\helpers\CompanyHelper;
use core\helpers\customer\CompanyCustomerHelper;
use core\helpers\finance\CompanyCashflowHelper;
use core\models\company\Cashback;
use core\models\company\Company;
use core\models\company\query\CompanyQuery;
use core\models\customer\CompanyCustomer;
use core\models\customer\CompanyCustomerPhone;
use core\models\customer\Customer;
use core\models\customer\CustomerRequest;
use core\models\customer\CustomerSubscription;
use core\models\customer\DelayedNotification;
use core\models\document\Document;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowPayment;
use core\models\Image;
use core\models\order\Order;
use core\models\order\OrderPayment;
use core\models\Payment;
use core\models\user\User;
use core\models\warehouse\Sale;
use core\models\warehouse\Usage;
use core\repositories\CompanyCashflowRepository;
use core\repositories\CompanyCashRepository;
use core\repositories\CompanyCostItemRepository;
use core\repositories\customer\{
    CompanyCustomerCategoryRepository, CompanyCustomerRepository
};
use core\repositories\CustomerRepository;
use core\repositories\InsuranceCompanyRepository;
use core\repositories\order\OrderRepository;
use core\repositories\user\UserRepository;
use core\services\dto\CustomerData;
use core\services\dto\CustomerInsuranceData;
use core\services\TransactionManager;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\web\HttpException;

class CompanyCustomerService
{
    private $companyCustomerRepository;
    private $companyCustomerCategoryRepository;
    private $customerRepository;
    private $transactionManager;
    private $orderRepository;
    private $companyCashflowRepository;
    private $companyCashRepository;
    private $companyCostItemRepository;
    private $userRepository;
    private $insuranceCompanies;

    private $orderDebtPayments;

    public function __construct(
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        CompanyCustomerRepository $companyCustomerRepository,
        CompanyCustomerCategoryRepository $companyCustomerCategoryRepository,
        CompanyCashflowRepository $companyCashflowRepository,
        CompanyCashRepository $companyCashRepository,
        CompanyCostItemRepository $companyCostItemRepository,
        UserRepository $userRepository,
        InsuranceCompanyRepository $insuranceCompanies,
        TransactionManager $transactionManager
    )
    {
        $this->customerRepository = $customerRepository;
        $this->companyCustomerRepository = $companyCustomerRepository;
        $this->companyCustomerCategoryRepository = $companyCustomerCategoryRepository;
        $this->transactionManager = $transactionManager;
        $this->orderRepository = $orderRepository;
        $this->companyCashflowRepository = $companyCashflowRepository;
        $this->companyCashRepository = $companyCashRepository;
        $this->companyCostItemRepository = $companyCostItemRepository;
        $this->userRepository = $userRepository;
        $this->insuranceCompanies = $insuranceCompanies;
    }

    /**
     * @param CustomerData          $customerData
     * @param string                $email
     * @param integer               $gender
     * @param string                $birth_date
     * @param string                $address
     * @param string                $city
     * @param array                 $categories
     * @param string                $comments
     * @param string                $sms_birthday
     * @param string                $sms_exclude
     * @param integer               $balance
     * @param integer               $company_id
     * @param string                $employer
     * @param string                $job
     * @param string                $iin
     * @param string                $id_card_number
     * @param UploadedFile          $imageFile
     * @param integer               $discount
     * @param integer               $cashback_percent
     * @param CustomerInsuranceData $customerInsuranceData
     *
     * @param array                 $phones
     *
     * @return CompanyCustomer
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function createCustomer(
        CustomerData $customerData,
        $email,
        $gender,
        $birth_date,
        $address,
        $city,
        $categories,
        $comments,
        $sms_birthday,
        $sms_exclude,
        $balance,
        $company_id,
        $employer,
        $job,
        $iin,
        $id_card_number,
        $imageFile,
        $discount,
        $cashback_percent,
        CustomerInsuranceData $customerInsuranceData,
        $phones = []
    ) {
        $customer = Customer::add(
            $customerData->phone,
            $customerData->name,
            $customerData->surname,
            $gender,
            $birth_date,
            $email,
            $iin,
            $id_card_number,
            $customerData->patronymic
        );

        $companyCustomer = CompanyCustomer::add(
            $customer,
            $company_id,
            $discount,
            $sms_birthday,
            $sms_exclude,
            $comments,
            $customerData->source_id,
            $address,
            $city,
            $balance,
            $job,
            $employer,
            null,
            null,
            $customerData->medical_record_id
        );
        $companyCustomer->cashback_percent = $cashback_percent;

        if (!empty($customerInsuranceData->insurance_company_id)) {
            $companyCustomer->setCustomerInsuranceCompany($this->insuranceCompanies->find($customerInsuranceData->insurance_company_id));
        }
        $companyCustomer->setInsuranceData($customerInsuranceData);

        if ($imageFile !== null && $image = Image::uploadImage($imageFile)) {
            $customer->image_id = $image->id;
        }

        $companyCustomer->categories = $this->getCategories($categories);
        $this->grantDiscount($companyCustomer);
        $this->grantCashbackPercent($companyCustomer);

        $this->transactionManager->execute(function () use ($categories, $customer, $companyCustomer, $phones) {
            $this->customerRepository->save($customer);
            $this->companyCustomerRepository->save($companyCustomer);
            $this->savePhones($companyCustomer, $phones);
            $this->companyCustomerRepository->save($companyCustomer);
        });

        $companyCustomer->refresh();

        return $companyCustomer;
    }

    /**
     * @param CustomerData $customerData
     * @param string $email
     * @param integer $gender
     * @param string $birth_date
     * @param string $address
     * @param string $city
     * @param array $categories
     * @param string $comments
     * @param string $sms_birthday
     * @param string $sms_exclude
     * @param integer $balance
     * @param string $employer
     * @param string $job
     * @param string $iin
     * @param string $id_card_number
     * @param UploadedFile $imageFile
     * @param integer $discount
     * @param                       $cashback_percent
     * @param CustomerInsuranceData $customerInsuranceData
     *
     * @param $phones
     * @return CompanyCustomer
     */
    public function updateProfile(
        CustomerData $customerData,
        $email,
        $gender,
        $birth_date,
        $address,
        $city,
        $categories,
        $comments,
        $sms_birthday,
        $sms_exclude,
        $balance,
        $employer,
        $job,
        $iin,
        $id_card_number,
        $imageFile,
        $discount,
        $cashback_percent,
        CustomerInsuranceData $customerInsuranceData,
        $phones = []
    ) {
        $companyCustomer = $this->companyCustomerRepository->find($customerData->company_customer_id);
        $customer = $this->customerRepository->find($companyCustomer->customer_id);

        $customer->edit(
            $customerData->phone,
            $email,
            $gender,
            $birth_date,
            $iin,
            $id_card_number
        );
        $customer->rename($customerData->name, $customerData->surname, $customerData->patronymic);

        $companyCustomer->edit(
            $address,
            $city,
            $customerData->source_id,
            $comments,
            $sms_birthday,
            $sms_exclude,
            $balance,
            $job,
            $employer,
            $discount,
            $cashback_percent,
            $customerData->medical_record_id
        );

        if (!empty($customerInsuranceData->insurance_company_id)) {
            $companyCustomer->setCustomerInsuranceCompany($this->insuranceCompanies->find($customerInsuranceData->insurance_company_id));
        }
        $companyCustomer->setInsuranceData($customerInsuranceData);

        if ($imageFile !== null && $image = Image::uploadImage($imageFile)) {
            $customer->image_id = $image->id;
        }

        $companyCustomer->categories = $this->getCategories($categories);
        $this->grantDiscount($companyCustomer);
        $this->grantCashbackPercent($companyCustomer);

        $this->transactionManager->execute(function () use (
            $categories,
            $customer,
            $companyCustomer,
            $phones
        ) {
            $this->customerRepository->save($customer);
            $this->companyCustomerRepository->save($companyCustomer);
            $this->companyCustomerRepository->unlinkAllCategories($companyCustomer->id);
            $this->linkCategories($categories, $companyCustomer->id);
            $this->grantDiscount($companyCustomer);
            $this->savePhones($companyCustomer, $phones);
        });

        $companyCustomer->refresh();

        return $companyCustomer;
    }

    public function changeAvatar($company_customer_id, $image_file) {
        if ($image_file === null) {
            throw new HttpException(400);
        }

        $companyCustomer = $this->companyCustomerRepository->find($company_customer_id);
        $customer = $this->customerRepository->find($companyCustomer->customer_id);

        $image = Image::uploadImage($image_file);

        if ($image === null) {
            throw new HttpException(500, 'No image found');
        }

        $customer->image_id = $image->id;

        $this->transactionManager->execute(function () use ($customer) {
            $this->customerRepository->save($customer);
        });

        $companyCustomer->refresh();

        return $companyCustomer;
    } 

    /**
     * Add new categories
     *
     * @param       $id
     * @param array $categories
     *
     * @throws \Exception
     */
    public function addCategories($id, $categories) {
        $companyCustomer = $this->companyCustomerRepository->find($id);

        $oldCategories = $companyCustomer->getCategories()->select('id')->column();
        $categories = array_unique(array_merge($categories, $oldCategories));

        $companyCustomer->categories = $this->getCategories($categories);
        $this->grantDiscount($companyCustomer);
        $this->grantCashbackPercent($companyCustomer);

        $this->transactionManager->execute(function () use ($companyCustomer) {
            $this->companyCustomerRepository->save($companyCustomer);
        });
    }

    /**
     * Pay customer debt
     *
     * @param integer   $company_customer_id
     * @param array     $payments
     * @param integer   $creator_id
     *
     * @param \DateTime $dateTime
     *
     * @return CompanyCustomer
     * @internal param int $amount
     *
     * @throws \Exception
     */
    public function payDebt($company_customer_id, array $payments, $creator_id, \DateTime $dateTime)
    {
        $creator = $this->userRepository->find($creator_id);

        $amount = array_sum($payments);
        $companyCustomer  = $this->companyCustomerRepository->find($company_customer_id);
        $companyCustomer->addBalance($amount);

        $orders = $this->orderRepository->findAllFinishedWithDebtByCompanyCustomer($companyCustomer->id);
        $orders = $this->getReducedOrderDebt($orders, $payments);

        $cashflows = $this->getDebtCashflows($amount, $orders, $companyCustomer, $creator, $dateTime);

        $this->transactionManager->execute(function () use ($companyCustomer, $orders, $cashflows) {
            $this->companyCustomerRepository->save($companyCustomer);
            foreach ($orders as $order) {
                $this->orderRepository->save($order);
                foreach ($order->orderPayments as $orderPayment) {
                    if ($orderPayment->isNewRecord || $orderPayment->isAttributeChanged('amount')) {
                        $this->companyCashflowRepository->save($orderPayment);
                    }
                }
            }
            foreach ($cashflows as $cashflow) {
                $this->companyCashflowRepository->save($cashflow);
            }
        });

        return $companyCustomer;
    }

    /**
     * @deprecated
     * Link with categories
     *
     * @param array   $categories
     * @param integer $company_customer_id
     *
     * @throws \yii\db\Exception
     */
    final protected function linkCategories($categories, $company_customer_id)
    {
        foreach ($categories as $category_id) {
            $category = $this->companyCustomerCategoryRepository->find($category_id);
            $this->companyCustomerRepository->linkCategory($company_customer_id, $category_id);
        }
    }

    /**
     * @param $categories
     * @return array
     */
    final protected function getCategories($categories)
    {
        return array_map(function (int $category_id) {
            return $this->companyCustomerCategoryRepository->find($category_id);
        }, $categories);
    }

    /**
     * @ToDo include loyaltes' discounts
     * @param CompanyCustomer $companyCustomer
     */
    final protected function grantDiscount(CompanyCustomer $companyCustomer)
    {
        if ($companyCustomer->discount_granted_by == CompanyCustomer::GRANTED_BY_CATEGORY) {
            $companyCustomer->discount = 0;
        }

        foreach ($companyCustomer->categories as $category) {
            if ($category->discount > $companyCustomer->discount) {
                $companyCustomer->discount = $category->discount;
                $companyCustomer->discount_granted_by = CompanyCustomer::GRANTED_BY_CATEGORY;
            }
        }
    }

    /**
     * @param CompanyCustomer $companyCustomer
     */
    final protected function grantCashbackPercent(CompanyCustomer $companyCustomer)
    {
        foreach ($companyCustomer->categories as $category) {
            if ($category->cashback_percent > $companyCustomer->cashback_percent) {
                $companyCustomer->cashback_percent = $category->cashback_percent;
            }
        }
    }

    /**
     * @param Order[] $orders
     *
     * @param array $debtPayments
     * @return Order[]
     */
    private function getReducedOrderDebt($orders, array $debtPayments)
    {
        $sumOfPayments = array_sum($debtPayments);
        return array_map(function (Order $order) use (&$sumOfPayments, $debtPayments) {
            if ($order->debtExists() && $sumOfPayments > 0) {
                $this->orderDebtPayments[$order->id] = [];
                $debt = $order->payment_difference;
                $order->editPaymentDifference(
                    min(0, $debt + $sumOfPayments)
                );

                $orderPayments = $order->orderPayments;
                foreach ($debtPayments as $payment_id => $sum) {
                    if ($debt >= 0) {
                        break;
                    }

                    if ($sum <= 0) {
                        continue;
                    }

                    $paymentAmount = min(abs($debt), $sum);
                    $debt += $paymentAmount;
                    $sumOfPayments -= $paymentAmount;
                    $debtPayments[$payment_id] -= $paymentAmount;

                    $found = false;
                    foreach ($orderPayments as $orderPayment) {
                        if ($orderPayment->payment_id == $payment_id) {
                            $orderPayment->amount += $paymentAmount;
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $orderPayments[] = OrderPayment::add($order, Payment::findOne($payment_id), $paymentAmount);
                    }

                    $this->orderDebtPayments[$order->id][$payment_id] = $paymentAmount;
                }

                $order->setPayments($orderPayments);

                $sumOfPayments = max(0, $order->payment_difference + $sumOfPayments);
            }

            return $order;
        }, $orders);
    }

    /**
     * Exports CustomerCompany and Customer details from the given ActiveDataProvider
     *
     * @param ActiveDataProvider $provider
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export(ActiveDataProvider $provider)
    {
        /** @var CompanyQuery $companyCustomers */
        $companyCustomers = $provider->query;

        $staticCustomer = new Customer();
        $staticCompanyCustomer = new CompanyCustomer();

        $data = [];
        if ($companyCustomers->count() > 0) {

            $data[] = new ExcelRow([
                "#",
                $staticCustomer->getAttributeLabel('name'),
                $staticCustomer->getAttributeLabel('lastname'),
                $staticCustomer->getAttributeLabel('phone'),
                $staticCompanyCustomer->getAttributeLabel('discount'),
                $staticCustomer->getAttributeLabel('gender'),
                $staticCustomer->getAttributeLabel('birth_date'),
                $staticCompanyCustomer->getAttributeLabel('city'),
                $staticCompanyCustomer->getAttributeLabel('address'),
                $staticCustomer->getAttributeLabel('email'),
                $staticCompanyCustomer->getAttributeLabel('comments'),
                $staticCompanyCustomer->getAttributeLabel('categories'),
                $staticCompanyCustomer->getAttributeLabel('sms_birthday'),
                $staticCompanyCustomer->getAttributeLabel('sms_exclude'),
                \Yii::t('app', 'Last Visit Date'),
                'Сотрудник во время последнего визита'
            ], true);

            $counter = 1;
            foreach ($companyCustomers->each(100) as $companyCustomer) {
                /** @var CompanyCustomer $companyCustomer */
                $customer = $companyCustomer->customer;
                $companyCategoryNames = $companyCustomer->getCategories()->select('name')->column();
                $lastVisit = $companyCustomer->getLastVisitDateTime();

                $data[] = new ExcelRow([
                    $counter++,
                    $customer->name,
                    $customer->lastname,
                    $customer->phone,
                    $companyCustomer->discount,
                    $customer->getGenderName(),
                    $customer->birth_date,
                    $companyCustomer->city,
                    $companyCustomer->address,
                    $customer->email,
                    $companyCustomer->comments,
                    implode(';', $companyCategoryNames),
                    CompanyCustomerHelper::getSmsOptionLabels()[$companyCustomer->sms_birthday],
                    CompanyCustomerHelper::getSmsOptionLabels()[$companyCustomer->sms_exclude],
                    $lastVisit ? \Yii::$app->formatter->asDatetime($lastVisit) : null,
                    $lastVisit ? $companyCustomer->lastOrder->staff->getFullName() : null
                ]);
            }
        }

        $filename = \Yii::t('app', 'Customers') . "_" . date("d-m-Y-His");

        \Yii::$app->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                \Yii::$app->name,
                \Yii::t('app', 'Customers')
            ),
            $data
        );
    }

    public function exportLost(ActiveDataProvider $dataProvider)
    {
        /** @var CompanyQuery $companyCustomers */
        $companyCustomers = $dataProvider->query;

        $staticCustomer = new Customer();

        $data = [];
        if ($companyCustomers->count() > 0) {

            $data[] = new ExcelRow([
                "#",
                $staticCustomer->getAttributeLabel('name'),
                $staticCustomer->getAttributeLabel('lastname'),
                $staticCustomer->getAttributeLabel('phone'),
                \Yii::t('app', 'Last Visit Date'),
                'Сотрудник во время последнего визита',
                'Филиал',
                'Выручка',
            ], true);

            $counter = 1;
            foreach ($companyCustomers->each(100) as $companyCustomer) {
                /** @var CompanyCustomer $companyCustomer */
                $customer = $companyCustomer->customer;
                $lastVisit = $companyCustomer->lastOrder;

                $data[] = new ExcelRow([
                    $counter++,
                    $customer->name,
                    $customer->lastname,
                    $customer->phone,
                    $lastVisit ? \Yii::$app->formatter->asDatetime($companyCustomer->getLastVisitDateTime()) : null,
                    $lastVisit ? $companyCustomer->lastOrder->staff->getFullName() : null,
                    $lastVisit ? $companyCustomer->lastOrder->division->name : null,
                    $lastVisit ? $companyCustomer->lastOrder->income : 0,
                ]);
            }
        }

        $filename = \Yii::t('app', 'Lost Customers') . "_" . date("d-m-Y-H-i-s");

        \Yii::$app->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                \Yii::$app->name,
                \Yii::t('app', 'Lost Customers')
            ),
            $data
        );
    }

    /**
     * Send CustomRequest to multiple companyCustomers
     *
     * @param CompanyCustomer[] $companyCustomers
     * @param Company           $company
     * @param string            $message
     *
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     */
    public function sendRequest($companyCustomers, $company, $message)
    {
        $smsSum = sizeof($companyCustomers) * CompanyHelper::estimateSmsPrice(strval($message));
        $balance = $company->getBalance();

        if (!$company->unlimited_sms && $smsSum > $balance) {
            throw new ForbiddenHttpException("У Вас недостаточно средств, чтобы отправить SMS." .
                    "\nДля отправки требуется {$smsSum} тг. Ваш баланс {$balance} тг." .
                    "\nПополните пожалуйста баланс в разделе Настройки.");
        }

        $incorrect = [];
        foreach ($companyCustomers as $companyCustomer) {
            if ($companyCustomer AND $message) { // TODO why AND?
                if (! CustomerRequest::sendCustomRequest($companyCustomer, $company, $message)) {
                    $incorrect[] = $companyCustomer->customer->phone;
                }
            }
        }

        if (empty($incorrect)) {
            return true;
        } else {
            throw new ForbiddenHttpException("Произошла ошибка при отправке SMS данным клиентам: " .
                implode(", ", $incorrect));
        }
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function restore(int $id)
    {
        $companyCustomer = $this->companyCustomerRepository->find($id);
        $companyCustomer->restore();

        $this->transactionManager->execute(function () use ($companyCustomer) {
            $this->companyCustomerRepository->save($companyCustomer);
        });
    }

    /**
     * @param int $amount
     * @param Order[] $orders
     * @param CompanyCustomer $companyCustomer
     * @param User $creator
     * @return CompanyCashflow[]
     */
    private function getDebtCashflows(int $amount, $orders, CompanyCustomer $companyCustomer, User $creator, \DateTime $dateTime)
    {
        $cashflows = [];
        if (isset($orders[0])) {
            $costItem = $this->companyCostItemRepository->findDebtPaymentCostItemByCompany($creator->company_id);

            foreach ($orders as $order) {
                $paid = $order->payment_difference - $order->getOldAttribute('payment_difference');
                if ($paid <= 0) {
                    continue;
                }
                $cashflow = CompanyCashflow::add(
                    $dateTime->format("Y-m-d H:i:s"),
                    $order->company_cash_id,
                    "Оплата долга за запись №{$order->number}",
                    $creator->company_id,
                    null,
                    $costItem->id,
                    $order->company_customer_id,
                    $order->division_id,
                    CompanyCashflow::RECEIVER_STAFF,
                    $order->staff_id,
                    $paid,
                    $creator->id
                );
                $cashflow->setOrderRelation($order);
                $cashflowPayments = [];
                foreach ($this->orderDebtPayments[$order->id] as $payment_id => $amount) {
                    $cashflowPayments[] = CompanyCashflowPayment::add($cashflow, $payment_id, $amount);
                }
                $cashflow->payments = $cashflowPayments;

                $cashflows[] = $cashflow;
            }
        } else {
            $division = $creator->company->divisions[0];
            $companyCash = $this->companyCashRepository->findFirstByDivision($division->id);
            $division_id = $division->id;
            $company_cash_id = $companyCash->id;

            $costItem = $this->companyCostItemRepository->findDebtPaymentCostItemByCompany($creator->company_id);
            $customer_name = $companyCustomer->customer->getFullName() . ' (' . $companyCustomer->customer->phone . ')';

            $cashflow = CompanyCashflow::add(
                date('Y-m-d H:i:s'),
                $company_cash_id,
                CompanyCashflowHelper::getDebtPaymentComment($customer_name, abs($companyCustomer->balance)),
                $companyCustomer->company_id,
                null,
                $costItem->id,
                $companyCustomer->id,
                $division_id,
                CompanyCashflow::RECEIVER_STAFF,
                null,
                $amount,
                $creator->id
            );
            $cashflowPayment = CompanyCashflowPayment::add($cashflow, Payment::CASH_ID, $amount);
            $cashflow->payments = [$cashflowPayment];
            $cashflows[] = $cashflow;
        }

        return $cashflows;
    }

    /**
     * @param int   $id
     * @param array $merged_customer_ids
     *
     * @return CompanyCustomer
     * @throws \Exception
     */
    public function merge(int $id, array $merged_customer_ids)
    {
        $companyCustomer = $this->companyCustomerRepository->find($id);

        /* @var CompanyCustomer[] $mergedCompanyCustomers */
        $mergedCompanyCustomers = array_map(function (int $merged_customer_id) {
            return $this->companyCustomerRepository->find($merged_customer_id);
        }, $merged_customer_ids);

        foreach ($mergedCompanyCustomers as $mergedCompanyCustomer) {
            $mergedCompanyCustomer->softDelete();
        }

        $this->transactionManager->execute(function () use (
            $companyCustomer,
            $mergedCompanyCustomers
        ) {

            $company_customer_ids = array_map(function (
                CompanyCustomer $companyCustomer
            ) {
                return $companyCustomer->id;
            }, $mergedCompanyCustomers);

            $customer_ids = array_map(function (CompanyCustomer $companyCustomer
            ) {
                return $companyCustomer->customer->id;
            }, $mergedCompanyCustomers);

            Order::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            Document::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            Usage::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            CompanyCashflow::updateAll(['customer_id' => $companyCustomer->id],
                ['customer_id' => $company_customer_ids]);
            Cashback::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            CustomerRequest::updateAll(['customer_id' => $companyCustomer->customer_id],
                [
                    'customer_id' => $customer_ids,
                    'company_id'  => $companyCustomer->company_id
                ]);
            Sale::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            CustomerSubscription::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);
            DelayedNotification::updateAll(['company_customer_id' => $companyCustomer->id],
                ['company_customer_id' => $company_customer_ids]);

            foreach ($mergedCompanyCustomers as $mergedCompanyCustomer) {
                $companyCustomer->balance += $mergedCompanyCustomer->balance;
                $companyCustomer->cashback_balance += $mergedCompanyCustomer->cashback_balance;
            }

            $this->companyCustomerRepository->save($companyCustomer);

            foreach ($mergedCompanyCustomers as $mergedCompanyCustomer) {
                $this->companyCustomerRepository->save($mergedCompanyCustomer);
            }
        });

        return $companyCustomer;
    }

    /**
     * @param $phone
     * @param $company_id
     * @return CompanyCustomer $customer
     */
    public function findByPhone($phone, $company_id)
    {
        return $this->companyCustomerRepository->findByPhone($phone, $company_id);
    }

    /**
     * @param CompanyCustomer $companyCustomer
     * @param $phones
     */
    private function savePhones(CompanyCustomer $companyCustomer, $phones)
    {
        CompanyCustomerPhone::deleteAll([
            'AND',
            ['company_customer_id' => $companyCustomer->id],
            ['not in', 'phone', $phones]
        ]);

        foreach ($phones as $phone) {
            $customerPhone = $this->companyCustomerRepository->findPhone($companyCustomer->id, $phone);

            if (!$customerPhone) {
                $customerPhone = new CompanyCustomerPhone([
                    'company_customer_id' => $companyCustomer->id,
                    'phone'               => $phone
                ]);
                $this->companyCashflowRepository->add($customerPhone);
            }
        }
    }
}

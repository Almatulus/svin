<?php

namespace core\tests;

use core\helpers\AppHelper;
use core\helpers\company\CashbackHelper;
use core\helpers\division\ServiceHelper;
use core\helpers\finance\CompanyCostItemHelper;
use core\helpers\GenderHelper;
use core\helpers\medCard\MedCardToothHelper;
use core\helpers\order\OrderConstants;
use core\helpers\ScheduleTemplateHelper;
use core\models\City;
use core\models\company\Cashback;
use core\models\company\Company;
use core\models\company\CompanyPosition;
use core\models\company\Insurance;
use core\models\company\Referrer;
use core\models\company\Tariff;
use core\models\company\TariffPayment;
use core\models\CompanyPaymentLog;
use core\models\ConfirmKey;
use core\models\Country;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerContact;
use core\models\customer\CustomerLoyalty;
use core\models\customer\CustomerRequestTemplate;
use core\models\customer\CustomerSource;
use core\models\customer\DelayedNotification;
use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\models\division\DivisionService;
use core\models\division\DivisionServiceInsuranceCompany;
use core\models\division\DivisionServiceProduct;
use core\models\document\DentalCardElement;
use core\models\document\Document;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormCompanyPositionMap;
use core\models\document\DocumentFormElement;
use core\models\document\DocumentFormPositionMap;
use core\models\document\DocumentTemplate;
use core\models\document\DocumentValue;
use core\models\File;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowPayment;
use core\models\finance\CompanyCashflowService;
use core\models\finance\CompanyContractor;
use core\models\finance\CompanyCostItem;
use core\models\finance\Payroll;
use core\models\finance\PayrollService;
use core\models\finance\PayrollStaff;
use core\models\InsuranceCompany;
use core\models\medCard\MedCard;
use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCommentCategory;
use core\models\medCard\MedCardCompanyComment;
use core\models\medCard\MedCardDiagnoseClass;
use core\models\medCard\MedCardDiagnosis;
use core\models\medCard\MedCardTab;
use core\models\medCard\MedCardTabComment;
use core\models\medCard\MedCardTabService;
use core\models\medCard\MedCardTooth;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\NewsLog;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\models\order\OrderDocumentTemplate;
use core\models\order\OrderPayment;
use core\models\order\OrderProduct;
use core\models\order\OrderService;
use core\models\Payment;
use core\models\Position;
use core\models\ScheduleTemplate;
use core\models\ScheduleTemplateInterval;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\StaffPayment;
use core\models\StaffPaymentService;
use core\models\StaffReview;
use core\models\StaffSchedule;
use core\models\user\User;
use core\models\user\UserDivision;
use core\models\warehouse\Category;
use core\models\warehouse\Delivery;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;
use core\models\warehouse\ProductUnit;
use core\models\warehouse\Stocktake;
use core\models\warehouse\StocktakeProduct;
use core\models\warehouse\Usage;
use core\models\warehouse\UsageProduct;
use Faker\Factory;
use Faker\Generator;
use saada\FactoryMuffin\FactoryMuffin as SaadaFactoryMuffin;

class FactoryMuffinHelper extends \Codeception\Module
{
    /**
     * @var SaadaFactoryMuffin
     */
    private $factory;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @inheritDoc
     */
    public function getModule($name)
    {
        return parent::getModule($name);
    }

    /**
     * @inheritDoc
     */
    public function _initialize()
    {
        date_default_timezone_set('Asia/Almaty');

        $faker = $this->getFaker();

        $this->factory = new SaadaFactoryMuffin();

        $this->factory->define(Country::class)->setDefinitions([
            'name' => $faker->name,
        ]);

        $this->factory->define(City::class)->setDefinitions([
            'name'       => $faker->name,
            'country_id' => 'factory|' . Country::class,
        ]);

        $this->factory->define(Tariff::class)->setDefinitions([
            'name'      => $faker->name,
            'staff_qty' => $faker->randomNumber(1),
            'price'     => $faker->randomNumber(5)
        ]);

        $this->factory->define(TariffPayment::class)->setDefinitions([
            'sum'        => $faker->randomNumber(3),
            'company_id' => 'factory|' . Company::class,
            'period'     => $faker->randomNumber(1),
            'start_date' => $this->faker->dateTimeBetween('-2 months', '-1 month')->format("Y-m-d"),
            'created_at' => gmdate("Y-m-d H:i:s"),
        ]);

        $this->factory->define(Company::class)->setDefinitions([
            'tariff_id'          => 'factory|' . Tariff::class,
            'name'               => $faker->text(),
            'status'             => Company::STATUS_ENABLED,
            'logo_id'            => 1,
            'head_name'          => $faker->name,
            'category_id'        => 'factory|'.ServiceCategory::class,
        ])->setCallback(function ($object, $saved) {
            if ($saved) {
                $costItems = CompanyCostItemHelper::getInitialItems();
                array_walk($costItems, function ($item) use ($object) {
                    $this->factory->create(CompanyCostItem::class, [
                        'company_id'     => $object->id,
                        'name'           => $item['name'],
                        'type'           => $item['type'],
                        'cost_item_type' => $item['cost_item_type'],
                    ]);
                });
            }
        });

        $this->factory->define(CompanyPosition::class)->setDefinitions([
            'company_id'  => 'factory|' . Company::class,
            'name'        => $faker->name,
            'description' => $faker->text
        ]);

        $this->factory->define(Position::class)->setDefinitions([
            'name'                => $faker->name,
            'description'         => $faker->text,
            'service_category_id' => 'factory|' . ServiceCategory::class
        ]);

        $this->factory->define(DocumentFormPositionMap::class)->setDefinitions([
            'document_form_id'  => 'factory|' . DocumentForm::class,
            'position_id'       => 'factory|' . Position::class
        ]);

        $this->factory->define(DocumentFormCompanyPositionMap::class)->setDefinitions([
            'document_form_id'  => 'factory|' . DocumentForm::class,
            'company_position_id'       => 'factory|' . CompanyPosition::class
        ]);

        $this->factory->define(CompanyPaymentLog::class)->setDefinitions([
            'company_id'     => 'factory|' . Company::class,
            'description'    => $faker->text,
            'value'          => $faker->randomNumber(2),
            'currency'       => CompanyPaymentLog::CURRENCY_KZT,
            'code'           => $faker->text,
            'created_time'   => date("Y-m-d H:i:s"),
            'confirmed_time' => date("Y-m-d H:i:s"),
            'message'        => $faker->text
        ]);

        $this->factory->define(CompanyCostItem::class)->setDefinitions([
            'company_id'     => 'factory|' . Company::class,
            'name'           => $faker->name,
            'type'           => function () use ($faker) {
                return $faker->randomElement([
                    CompanyCostItem::TYPE_ALL,
                    CompanyCostItem::TYPE_INCOME,
                    CompanyCostItem::TYPE_EXPENSE,
                ]);
            },
            'comments'       => $faker->text,
            'cost_item_type' => null,
        ]);

        $this->factory->define(ServiceCategory::class)->setDefinitions([
            'name' => $faker->name,
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC,
        ]);

        $this->factory->define(InsuranceCompany::class)->setDefinitions([
            'name' => $faker->name
        ]);

        $this->factory->define(DivisionServiceInsuranceCompany::class)->setDefinitions([
            'division_service_id'  => 'factory|' . DivisionService::class,
            'insurance_company_id' => 'factory|' . InsuranceCompany::class,
            'price'                => $faker->numberBetween(100, 1000),
            'price_max'            => $faker->numberBetween(1000, 10000)
        ]);

        $this->factory->define(Referrer::class)->setDefinitions([
            'name' => $faker->firstName,
        ]);

        $this->defineDivision();
        $this->defineWarehouse();
        $this->defineFinance();
        $this->defineStaff();

        $this->factory->define(User::class)->setDefinitions([
            'username'      => function () use ($faker) {
                return $faker->regexify("\+7 \d{3} \d{3} \d{2} \d{2}");
            },
            'salt'          => "",
            'password_hash' => '$2y$13$UE8xrcDcbPcMhHMCokWRGOkmO7KTLsFlwLHiUo27O8cFxJOHKDTwy',
            'auth_key'      => function () use ($faker) {
                return $faker->md5;
            },
            'access_token'  => function () use ($faker) {
                return $faker->md5 . '_' . time();
            },
            'company_id'    => 'factory|' . Company::class,
            'status'        => User::STATUS_ENABLED,
        ]);

        $this->factory->define(\core\models\rbac\AuthAssignment::class)->setDefinitions([
            'item_name'  => $faker->word,
            'user_id'    => 'factory|' . User::class,
            'created_at' => time()
        ]);

        $this->factory->define(MedCardCommentCategory::class)->setDefinitions([
            'name'                => $faker->word,
            'parent_id'           => null,
            'service_category_id' => 'factory|' . ServiceCategory::class,
        ]);

        $this->factory->define(MedCardComment::class)->setDefinitions([
            'category_id' => 'factory|' . MedCardCommentCategory::class,
            'comment'     => $faker->text(),
        ]);

        $this->factory->define(MedCardCompanyComment::class)->setDefinitions([
            'company_id'  => 'factory|' . Company::class,
            'category_id' => 'factory|' . MedCardCommentCategory::class,
            'comment'     => $faker->text(),
        ]);

        $this->factory->define(MedCardDiagnosis::class)->setDefinitions([
            'name'     => $faker->text(),
            'code'     => function () use ($faker) {
                return $faker->unique()->regexify('[A-Z]\d\d\.\d');
            },
            'class_id' => 'factory|' . MedCardDiagnoseClass::class,
        ]);

        $this->factory->define(MedCardDiagnoseClass::class)->setDefinitions([
            'name'      => $faker->text(),
            'parent_id' => null,
            'code'      => function () use ($faker) {
                return strval($faker->unique()->numberBetween(0, 999999));
            },
        ]);

        $this->factory->define(ConfirmKey::class)->setDefinitions([
            'code'       => $faker->randomNumber(3),
            'username'   => $faker->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            'expired_at' => function () {
                return date('Y-m-d H:i:s', ConfirmKey::EXPIRE_TIME + time());
            },
            'status'     => ConfirmKey::STATUS_ENABLED,
        ]);

        $this->factory->define(CustomerCategory::class)->setDefinitions([
            'name'       => function () use ($faker) {
                return $faker->name;
            },
            'company_id' => 'factory|' . Company::class,
            'color'      => '#888888',
            'discount'   => $faker->numberBetween(0, 100),
        ]);

        $this->factory->define(CustomerSource::class)->setDefinitions([
            'name'       => $faker->name,
            'company_id' => 'factory|' . Company::class,
            'type'       => CustomerSource::TYPE_DYNAMIC,
        ]);

        $this->factory->define(Insurance::class)->setDefinitions([
            'name'                 => $faker->name,
            'company_id'           => 'factory|' . Company::class,
            'insurance_company_id' => 'factory|' . InsuranceCompany::class,
            'description'          => $faker->realText(),
            'deleted_time'         => null,
        ]);

        $this->defineCustomer();

        $this->defineOrder();

        $this->factory->define(Payment::class)->setDefinitions([
            'name'   => $faker->name,
            'status' => Payment::STATUS_ENABLED,
        ]);

        $this->factory->define(NewsLog::class)->setDefinitions([
            'status' => NewsLog::STATUS_ENABLED,
            'text'   => $faker->realText(),
            'link'   => $faker->url,
        ]);

        $this->factory->define(DelayedNotification::class)->setDefinitions([
            'interval'            => $this->faker->randomElement(ServiceHelper::getIntervals()),
            'date'                => date("Y-m-d"),
            'status'              => DelayedNotification::STATUS_NEW,
            'company_customer_id' => 'factory|' . CompanyCustomer::class,
            'division_service_id' => 'factory|' . DivisionService::class,
            'created_at'          => date("Y-m-d H:i:s")
        ]);

        $this->factory->define(CustomerLoyalty::class)->setDefinitions([
            'event'       => CustomerLoyalty::EVENT_MONEY,
            'amount'      => $faker->numberBetween(0, 50000),
            'discount'    => $faker->numberBetween(0, 100),
            'rank'        => 0,
            'category_id' => 'factory|' . CustomerCategory::class,
            'mode'        => CustomerLoyalty::MODE_ADD_DISCOUNT,
            'company_id'  => 'factory|' . Company::class,
        ]);

        $this->factory->define(File::class)->setDefinitions([
            'path'       => function () use ($faker) {
                return $faker->unique()->url;
            },
            'name'       => function () use ($faker) {
                return $faker->unique()->text(32);
            },
            'created_at' => gmdate("Y-m-d H:i:s")
        ]);
    }

    /**
     * @inheritdoc
     */
    private function defineCustomer()
    {
        $faker = $this->faker;
        $this->factory->define(Customer::class)->setDefinitions([
            'phone'      => function () use ($faker) {
                return $faker->regexify("\+7 \d{3} \d{3} \d{2} \d{2}");
            },
            'birth_date' => $this->faker->date(),
            'name'       => $faker->name,
            'lastname'   => $faker->lastName,
            'email'      => $faker->email,
            'gender'     => function () use ($faker) {
                return $faker->randomElement(array_keys(GenderHelper::getGenders()));
            },
        ]);

        $this->factory->define(CustomerContact::class)->setDefinitions([
            'contact_id'  => 'factory|' . Customer::class,
            'customer_id' => 'factory|' . Customer::class,
        ]);

        $this->factory->define(CompanyCustomer::class)->setDefinitions([
            'company_id'  => 'factory|' . Company::class,
            'customer_id' => 'factory|' . Customer::class,
            'balance'     => $this->faker->randomNumber(3),
        ]);

        $this->factory->define(CustomerRequestTemplate::class)->setDefinitions([
            'key'         => function () use ($faker) {
                return strval($faker->randomKey(CustomerRequestTemplate::getDefaultEnabledTemplate()));
            },
            'is_enabled'  => true,
            'template'    => $this->faker->text(20),
            'company_id'  => 'factory|' . Company::class,
            'description' => $this->faker->text(20),
        ]);

        $this->defineDocument();
        $this->defineUsage();
    }

    /**
     * @inheritdoc
     */
    private function defineOrder()
    {
        $this->factory->define(Order::class)->setDefinitions([
            'company_cash_id'     => 'factory|' . CompanyCash::class,
            'company_customer_id' => 'factory|' . CompanyCustomer::class,
            'division_id'         => 'factory|' . Division::class,
            'staff_id'            => 'factory|' . Staff::class,
            'type'                => $this->faker->randomElement(OrderConstants::getTypes()),
            'created_user_id'     => 'factory|' . User::class,
            'datetime'            => gmdate('Y-m-d H:i:s'),
            //            'note'                => '',
            'hours_before'        => 0,
            'created_time'        => gmdate("Y-m-d H:i:s"),
            //            'color'               => '',
            //            'insurance_company_id'        => '',
            //            'referrer_id'         => ''
        ]);

        $this->factory->define(OrderService::class)->setDefinitions([
            'order_id'            => 'factory|' . Order::class,
            'division_service_id' => 'factory|' . DivisionService::class,
            'discount'            => 0,
            'price'               => $this->faker->randomNumber(3),
            'duration'            => rand(15, 120),
            'quantity'            => rand(1, 100),
        ]);

        $this->factory->define(OrderProduct::class)->setDefinitions([
            'order_id'       => 'factory|' . Order::class,
            'product_id'     => 'factory|' . Product::class,
            'purchase_price' => $this->faker->randomNumber(3),
            'selling_price'  => $this->faker->randomNumber(3),
            'quantity'       => rand(10, 100),
        ]);

        $this->factory->define(OrderPayment::class)->setDefinitions([
            'order_id'   => 'factory|' . Order::class,
            'payment_id' => 'factory|' . Payment::class,
            'amount'     => $this->faker->randomNumber(3),
        ]);

        $this->factory->define(OrderDocumentTemplate::class)->setDefinitions([
            'company_id'  => 'factory|' . Company::class,
            'name'        => $this->faker->name,
            'filename'    => $this->faker->name,
            'category_id' => 'factory|' . ServiceCategory::class,
            'path'        => $this->faker->name,
        ]);

        $this->factory->define(OrderDocument::class)->setDefinitions([
            'date'        => $this->faker->date(),
            'order_id'    => 'factory|' . Order::class,
            'template_id' => 'factory|' . OrderDocumentTemplate::class,
            'path'        => $this->faker->word,
            'user_id'     => 'factory|' . User::class,
        ]);

        $this->factory->define(MedCard::class)->setDefinitions([
            'order_id' => 'factory|' . Order::class
        ]);

        $this->factory->define(MedCardTab::class)->setDefinitions([
            'med_card_id' => 'factory|' . MedCard::class
        ]);

        $this->factory->define(MedCardTooth::class)->setDefinitions([
            'med_card_tab_id'    => 'factory|' . MedCardTab::class,
            'teeth_num'          => $this->faker->randomElement(MedCardToothHelper::allTeeth()),
            'mobility'           => $this->faker->randomNumber(1),
            'type'               => $this->faker->randomElement([MedCardTooth::TYPE_ADULT, MedCardTooth::TYPE_CHILD]),
            'teeth_diagnosis_id' => 'factory|' . MedCardToothDiagnosis::class,
        ]);

        $this->factory->define(MedCardTabComment::class)->setDefinitions([
            'med_card_tab_id' => 'factory|' . MedCardTab::class,
            'category_id'     => 'factory|' . MedCardCommentCategory::class,
            'comment'         => $this->faker->text(10),
        ]);

        $this->factory->define(MedCardTabService::class)->setDefinitions([
            'med_card_tab_id'     => 'factory|' . MedCardTab::class,
            'division_service_id' => 'factory|' . DivisionService::class,
            'quantity'            => $this->faker->randomNumber(1),
            'price'               => $this->faker->randomNumber(4),
            'created_user_id'     => 'factory|' . User::class
        ]);
    }

    /**
     * @inheritdoc
     */
    private function defineStaff()
    {
        $this->factory->define(Staff::class)->setDefinitions([
            'name'             => $this->faker->name,
            'document_scan_id' => 1,
            'gender'           => $this->faker->randomElement(array_keys(GenderHelper::getGenders())),
            'create_order'     => true,
            'status'           => Staff::STATUS_ENABLED,
            'color'            => 'color1',
        ]);

        $this->factory->define(StaffSchedule::class)->setDefinitions([
            'staff_id'    => 'factory|' . Staff::class,
            'division_id' => 'factory|' . Division::class,
            'start_at'    => date("Y-m-d 01:00:00"),
            'end_at'      => date("Y-m-d 23:00:00"),
        ]);

        $this->factory->define(ScheduleTemplate::class)->setDefinitions([
            'staff_id'      => 'factory|' . Staff::class,
            'division_id'   => 'factory|' . Division::class,
            'type'          => $this->faker->randomKey(ScheduleTemplateHelper::types()),
            'interval_type' => $this->faker->randomKey(ScheduleTemplateHelper::periods()),
            'created_at'    => date("Y-m-d H:i:s"),
            'updated_at'    => date("Y-m-d H:i:s"),
            'created_by'    => 'factory|' . User::class,
            'updated_by'    => 'factory|' . User::class
        ]);

        $this->factory->define(ScheduleTemplateInterval::class)->setDefinitions([
            'schedule_template_id' => 'factory|' . ScheduleTemplate::class,
            'day'                  => rand(1, 2),
            'start'                => "09:00",
            'end'                  => "22:00",
            'break_start'          => "14:00",
            'break_end'            => "15:00",
        ]);

        $this->factory->define(UserDivision::class)->setDefinitions([
            'staff_id'    => 'factory|' . Staff::class,
            'division_id' => 'factory|' . Division::class,
        ]);

        $this->factory->define(StaffReview::class)->setDefinitions([
            'customer_id'  => 'factory|' . Customer::class,
            'staff_id'     => 'factory|' . Staff::class,
            'created_time' => gmdate("Y-m-d H:i:s"),
            'value'        => $this->faker->numberBetween(0, StaffReview::REVIEW_LIMIT),
            'comment'      => $this->faker->text,
            'status'       => StaffReview::STATUS_ENABLED,
        ]);

        $this->factory->define(StaffPayment::class)->setDefinitions([
            'start_date' => $this->faker->dateTimeBetween('-2 months', '-1 month')->format("Y-m-d"),
            'end_date'   => $this->faker->date("Y-m-d"),
            'staff_id'   => 'factory|' . Staff::class,
            'salary'     => $this->faker->randomNumber(5),
            'created_at' => date("Y-m-d"),
            'updated_at' => date("Y-m-d"),
        ]);

        $this->factory->define(StaffPaymentService::class)->setDefinitions([
            'staff_payment_id' => 'factory|' . StaffPayment::class,
            'percent'          => $this->faker->numberBetween(0, 100),
            'sum'              => $this->faker->randomNumber(4),
            'payroll_id'       => 'factory|' . Payroll::class,
            'order_service_id' => 'factory|' . OrderService::class
        ]);
    }

    /**
     * @inheritdoc
     */
    private function defineDocument()
    {
        $faker = $this->faker;

        $this->factory->define(DocumentForm::class)->setDefinitions([
            'name'            => $this->faker->name,
            'has_dental_card' => $this->faker->boolean(50),
            'has_services'    => $this->faker->boolean(50),
        ]);

        $this->factory->define(DocumentFormElement::class)->setDefinitions([
            'document_form_id' => 'factory|' . DocumentForm::class,
            'order'            => $this->faker->randomNumber(),
            'raw_id'           => $this->faker->numberBetween(0, 100),
            'key'              => function () use ($faker) {
                return $faker->unique()->regexify('[a-z]{8}');
            },
            'type'             => function () use ($faker) {
                return $faker->randomElement([
                    DocumentFormElement::TYPE_TEXT_INPUT,
                    DocumentFormElement::TYPE_CHECKBOX,
                ]);
            },
            'label'            => $this->faker->name,
            'options'          => rand(0, 1) ? AppHelper::arrayToPg(...
                [$this->faker->name, $this->faker->name]) : null,
        ]);

        $this->factory->define(Document::class)->setDefinitions([
            'company_customer_id' => 'factory|' . CompanyCustomer::class,
            'document_form_id'    => 'factory|' . DocumentForm::class,
            'manager_id'          => 'factory|' . Staff::class,
            'staff_id'            => 'factory|' . Staff::class,
            'created_at'          => gmdate("Y-m-d H:i:s"),
            'updated_at'          => gmdate("Y-m-d H:i:s"),
        ]);

        $this->factory->define(DocumentValue::class)->setDefinitions([
            'document_id'              => 'factory|' . Document::class,
            'document_form_element_id' => 'factory|' . DocumentFormElement::class,
            'value'                    => $this->faker->text,
        ])->setCallback(function (DocumentValue $object, bool $saved) {
            if ($saved) {
                switch ($object->documentFormElement->type) {
                    case DocumentFormElement::TYPE_TEXT_INPUT:
                        $object->value = $this->faker->text();
                        break;
                    case DocumentFormElement::TYPE_CHECKBOX:
                        $object->value = rand(0, 1) ? "0" : "1";
                        break;
                }
                $object->save(false);
            }
        });

        $this->factory->define(MedCardToothDiagnosis::class)->setDefinitions([
            'company_id'   => 'factory|' . Company::class,
            'name'         => $this->faker->name,
            'abbreviation' => function () use ($faker) {
                return $faker->randomElement(MedCardToothHelper::getDiagnosisAbbreviations());
            },
            'color'        => $this->faker->hexColor,
        ]);

        $this->factory->define(DentalCardElement::class)->setDefinitions([
            'document_id'  => 'factory|' . Document::class,
            'diagnosis_id' => 'factory|' . MedCardToothDiagnosis::class,
            'number'       => function () use ($faker) {
                return $faker->randomElement(MedCardToothHelper::allTeeth());
            },
            'mobility'     => $this->faker->randomNumber(),
        ]);

        $this->factory->define(DocumentTemplate::class)->setDefinitions([
            'document_form_id' => 'factory|' . DocumentForm::class,
            'name'             => $this->faker->name,
            'values'           => json_encode([
                ["key" => $this->faker->name, "value" => $this->faker->name]
            ]),
            'created_by'       => 'factory|' . User::class,
            'created_at'       => date("Y-m-d H:i:s"),
        ]);
    }

    /**
     * @return \saada\FactoryMuffin\FactoryMuffin
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Define custom actions here
     *
     * @param string $locale
     *
     * @return \Faker\Generator
     */
    public function getFaker($locale = Factory::DEFAULT_LOCALE)
    {
        if (empty($this->faker)) {
            $this->faker = Factory::create($locale);
        }

        return $this->faker;
    }

    /**
     * @inheritdoc
     */
    private function defineDivision()
    {
        $faker = $this->faker;
        $this->factory->define(Division::class)->setDefinitions([
            'name'                      => $faker->name,
            'address'                   => $faker->address,
            'company_id'                => 'factory|' . Company::class,
            'city_id'                   => 'factory|' . City::class,
            'status'                    => Division::STATUS_ENABLED,
            'key'                       => function () use ($faker) {
                return $faker->md5;
            },
            'latitude'                  => $faker->latitude,
            'longitude'                 => $faker->longitude,
            'category_id'               => 'factory|' . ServiceCategory::class,
            'working_start'             => '09:00',
            'working_finish'            => '23:00',
            'default_notification_time' => 3,
        ])->setCallback(function ($object, $saved) {
            if ($saved) {
                /** @var $object Division */
                $companyCash = $this->getFactory()->create(CompanyCash::class, [
                    'division_id' => $object->id,
                    'company_id'  => $object->company_id,
                ]);
                $object->populateRelation('companyCash', $companyCash);
            }
        });

        $this->factory->define(DivisionPayment::class)->setDefinitions([
            'division_id' => 'factory|' . Division::class,
            'payment_id'  => 'factory|' . Payment::class
        ]);

        $this->factory->define(StaffDivisionMap::class)->setDefinitions([
            'division_id' => 'factory|' . Division::class,
            'staff_id'    => 'factory|' . Staff::class,
        ]);

        $this->factory->define(DivisionService::class)->setDefinitions([
            'price'              => $faker->randomNumber(3),
            'service_name'       => $faker->name,
            'average_time'       => $faker->numberBetween(15, 120),
            'notification_delay' => null
        ]);

        $this->factory->define(DivisionServiceProduct::class)->setDefinitions([
            'division_service_id' => 'factory|' . DivisionService::class,
            'product_id'          => 'factory|' . Product::class,
            'quantity'            => $faker->numberBetween(1, 100)
        ]);
    }

    private function defineUsage()
    {
        $this->factory->define(Usage::class)->setDefinitions([
            'company_id'          => 'factory|' . Company::class,
            'company_customer_id' => 'factory|' . CompanyCustomer::class,
            'discount'            => rand(0, 99),
            'division_id'         => 'factory|' . Division::class,
            'staff_id'            => 'factory|' . Staff::class,
            'status'              => Usage::STATUS_ACTIVE,
            'created_at'          => gmdate("Y-m-d H:i:s"),
            'updated_at'          => gmdate("Y-m-d H:i:s"),
        ]);

        $this->factory->define(UsageProduct::class)->setDefinitions([
            'quantity'       => $this->faker->randomNumber(),
            'product_id'     => 'factory|' . Product::class,
            'purchase_price' => $this->faker->randomNumber(),
            'selling_price'  => $this->faker->randomNumber(),
            'usage_id'       => 'factory|' . Usage::class,
        ]);
    }

    private function defineWarehouse()
    {
        $faker = $this->faker;

        $this->factory->define(Category::class)->setDefinitions([
            'name'       => $this->faker->name,
            'company_id' => 'factory|' . Company::class
        ]);

        $this->factory->define(Manufacturer::class)->setDefinitions([
            'name'       => $this->faker->name,
            'company_id' => 'factory|' . Company::class
        ]);

        $this->factory->define(ProductUnit::class)->setDefinitions([
            'name' => $this->faker->name,
        ]);

        $this->factory->define(ProductType::class)->setDefinitions([
            'name' => $this->faker->name,
        ]);

        $this->factory->define(Product::class)->setDefinitions([
            'division_id'    => 'factory|' . Division::class,
            'unit_id'        => 'factory|' . ProductUnit::class,
            'name'           => function () use ($faker) {
                return $faker->name;
            },
            'purchase_price' => $faker->randomNumber(3),
            'price'          => $faker->randomNumber(3),
            'quantity'       => $faker->randomNumber(2),
        ]);

        $this->factory->define(Delivery::class)->setDefinitions([
            'company_id' => 'factory|' . Company::class,
            'contractor_id' => 'factory|' . CompanyContractor::class,
            'creator_id' => 'factory|' . User::class,
            'division_id' => 'factory|' . Division::class,
            'notes' => $faker->text,
            'invoice_number' => strval($faker->randomNumber())
        ]);

        $this->factory->define(Stocktake::class)->setDefinitions([
            'type_of_products' => 'factory|' . ProductType::class,
            'company_id' => 'factory|' . Company::class,
            'category_id' => 'factory|' . Category::class,
            'creator_id' => 'factory|' . User::class,
            'division_id' => 'factory|' . Division::class,
            'name' => $faker->name,
            'description' => $faker->text(),
            'status' => Stocktake::STATUS_NEW
        ]);

        $this->factory->define(StocktakeProduct::class)->setDefinitions([
            'product_id' => 'factory|' . Product::class,
            'stocktake_id' => 'factory|' . Stocktake::class,
            'purchase_price' => $this->faker->numberBetween(1000, 10000),
            'recorded_stock_level' => $this->faker->numberBetween(10, 100),
            'actual_stock_level' => $this->faker->numberBetween(10, 100),
            'apply_changes' => true,
        ]);
    }

    private function defineFinance()
    {
        $faker = $this->faker;
        $this->factory->define(CompanyCash::class)->setDefinitions([
            'name'        => $faker->name,
            'company_id'  => 'factory|' . Company::class,
            'division_id' => 'factory|' . Division::class,
            'type'        => CompanyCash::TYPE_CASH_BOX,
            'status'      => CompanyCash::STATUS_ENABLED,
        ]);

        $this->factory->define(CompanyContractor::class)->setDefinitions([
            'type'        => function () use ($faker) {
                return $faker->randomElement(array_keys(CompanyContractor::getTypeLabels()));
            },
            'name'        => $faker->name,
            'division_id' => 'factory|' . Division::class,
        ]);

        $this->factory->define(CompanyCashflow::class)->setDefinitions([
            'date'          => date("Y-m-d H:i:s"),
            'cash_id'       => 'factory|' . CompanyCash::class,
            'company_id'    => 'factory|' . Company::class,
            'comment'       => $faker->text(40),
            'contractor_id' => 'factory|' . CompanyContractor::class,
            'cost_item_id'  => 'factory|' . CompanyCostItem::class,
            'customer_id'   => 'factory|' . CompanyCustomer::class,
            'division_id'   => 'factory|' . Division::class,
            'receiver_mode' => function () use ($faker) {
                return $faker->randomElement([
                    CompanyCashflow::RECEIVER_CUSTOMER,
                    CompanyCashflow::RECEIVER_CONTRACTOR,
                    CompanyCashflow::RECEIVER_STAFF
                ]);
            },
            'staff_id'      => 'factory|' . Staff::class,
            'value'         => $faker->randomNumber(3),
            'user_id'       => 'factory|' . User::class
        ]);

        $this->factory->define(CompanyCashflowPayment::class)->setDefinitions([
            'cashflow_id' => 'factory|' . CompanyCashflow::class,
            'payment_id'  => 'factory|' . Payment::class,
            'value'       => $faker->randomNumber(4)
        ]);

        $this->factory->define(CompanyCashflowService::class)->setDefinitions([
            'cashflow_id'      => 'factory|' . CompanyCashflow::class,
            'order_service_id' => 'factory|' . Order::class
        ]);

        $this->factory->define(Payroll::class)->setDefinitions([
            'name'              => $this->faker->name,
            'service_value'     => $this->faker->numberBetween(1, 100),
            'service_mode'      => $this->faker->randomElement(array_keys(Payroll::getModeLabels())),
            'salary'            => $this->faker->randomNumber(3),
            'salary_mode'       => $this->faker->randomElement(array_keys(Payroll::getPeriodLabels())),
            'is_count_discount' => true,
            'company_id'        => 'factory|' . Company::class,
        ]);
        $this->factory->define(PayrollStaff::class)->setDefinitions([
            'payroll_id'   => 'factory|' . Payroll::class,
            'staff_id'     => 'factory|' . Staff::class,
            'started_time' => date("Y-m-d"),
            'created_time' => date("Y-m-d")
        ]);
        $this->factory->define(PayrollService::class)->setDefinitions([
            'scheme_id'           => 'factory|' . Payroll::class,
            'division_service_id' => 'factory|' . DivisionService::class,
            'service_value'       => $this->faker->numberBetween(1, 100),
            'service_mode'        => $this->faker->randomElement(array_keys(Payroll::getModeLabels())),
        ]);

        $this->getFactory()->define(Cashback::class)->setDefinitions([
            'company_customer_id' => 'factory|' . CompanyCustomer::class,
            'amount'              => $this->faker->randomNumber(2),
            'percent'             => rand(1, 100),
            'status'              => CashbackHelper::STATUS_ENABLED,
            'type'                => $this->faker->randomElement(array_keys(CashbackHelper::getTypes())),
            'created_at'          => date("Y-m-d"),
            'updated_at'          => date("Y-m-d"),
            'created_by'          => 'factory|' . User::class,
            'updated_by'          => 'factory|' . User::class,
        ]);
    }
}

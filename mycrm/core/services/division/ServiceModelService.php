<?php

namespace core\services\division;

use common\components\excel\ExcelFileConfig;
use common\components\excel\ExcelRow;
use core\forms\division\ServiceCreateForm;
use core\forms\division\ServiceUpdateForm;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\DivisionServiceInsuranceCompany;
use core\models\division\DivisionServiceProduct;
use core\models\ServiceCategory;
use core\models\Staff;
use core\repositories\company\CompanyRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\division\DivisionServiceInsuranceCompanyRepository;
use core\repositories\division\DivisionServiceProductRepository;
use core\repositories\DivisionServiceRepository;
use core\repositories\ServiceCategoryRepository;
use core\repositories\StaffRepository;
use core\services\TransactionManager;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class ServiceModelService
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var DivisionServiceRepository
     */
    private $services;

    /**
     * @var DivisionServiceProductRepository
     */
    private $products;

    /**
     * @var StaffRepository
     */
    private $employees;

    /**
     * @var ServiceCategoryRepository
     */
    private $categories;

    /**
     * @var CompanyRepository
     */
    private $companies;

    /**
     * @var DivisionRepository
     */
    private $divisions;

    /**
     * @var DivisionServiceInsuranceCompanyRepository
     */
    private $insuranceCompanies;

    /**
     * ServiceModelService constructor.
     * @param DivisionServiceRepository $divisionServiceRepository
     * @param DivisionServiceProductRepository $divisionServiceProductRepository
     * @param ServiceCategoryRepository $serviceCategoryRepository
     * @param StaffRepository $staffRepository
     * @param CompanyRepository $companyRepository
     * @param DivisionRepository $divisionRepository
     * @param DivisionServiceInsuranceCompanyRepository $divisionServiceInsuranceCompanyRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        DivisionServiceRepository $divisionServiceRepository,
        DivisionServiceProductRepository $divisionServiceProductRepository,
        ServiceCategoryRepository $serviceCategoryRepository,
        StaffRepository $staffRepository,
        CompanyRepository $companyRepository,
        DivisionRepository $divisionRepository,
        DivisionServiceInsuranceCompanyRepository $divisionServiceInsuranceCompanyRepository,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager = $transactionManager;
        $this->categories = $serviceCategoryRepository;
        $this->employees = $staffRepository;
        $this->products = $divisionServiceProductRepository;
        $this->services = $divisionServiceRepository;
        $this->divisions = $divisionRepository;
        $this->companies = $companyRepository;
        $this->insuranceCompanies = $divisionServiceInsuranceCompanyRepository;
    }

    /**
     * @param ActiveDataProvider $provider
     */
    public function export(ActiveDataProvider $provider)
    {
        /** @var DivisionService[] $services */
        $services = $provider->getModels();

        $data = [];
        if (!empty($services)) {

            $data[] = new ExcelRow([
                'Полное название услуги',
                'Первичная',
                'Продолжительность(минут)',
                'Доступна для онлайн-записи',
                'Филиал',
                'Категория',
                'Цена(min)',
                'Описание услуги',
                'Страховая компания',
            ], true);

            foreach ($services as $service) {
                $data[] = new ExcelRow([
                    $service->service_name,
                    $service->is_trial ? '+' : '-',
                    $service->average_time,
                    $service->publish ? 'да' : 'нет',
                    implode(", ", ArrayHelper::getColumn($service->divisions, 'name')),
                    implode("; ", ArrayHelper::getColumn($service->categories, 'name')),
                    $service->price,
                    $service->description,
                    $service->insuranceCompany ? $service->insuranceCompany->name : '',
                ]);
            }
        }

        $filename = \Yii::t('app', 'Services') . "_" . date("d-m-Y-His");

        \Yii::$app->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                \Yii::$app->name,
                \Yii::t('app', 'Services')
            ),
            $data
        );
    }

    /**
     * @param ServiceCreateForm        $form
     * @param DivisionServiceProduct[] $products
     * @param DivisionServiceInsuranceCompany[] $insuranceCompanies
     *
     * @return DivisionService
     * @throws \Exception
     */
    public function create(ServiceCreateForm $form, $products, $insuranceCompanies)
    {
        $service = new DivisionService();
        $service->attributes = $form->attributes;

        $categories = $this->getCategories($form->category_ids);
        $divisions = $this->getDivisions($form->division_ids);

        $staff = $this->getStaff($form->staff);

        $this->transactionManager->execute(function () use (
            $service,
            $categories,
            $staff,
            $products,
            $divisions,
            $insuranceCompanies
        ) {

            $this->services->add($service);

            foreach ($products as $product) {
                $service->link('products', $product);
            }

            foreach ($insuranceCompanies as $insuranceCompany) {
                $service->link('insuranceCompanies', $insuranceCompany);
            }

            $this->saveCategories($service, $categories);
            $this->saveDivisions($service, $divisions);
            $this->saveStaff($service, $staff);
        });

        return $service;
    }

    /**
     * @param int                      $id
     * @param ServiceUpdateForm        $form
     * @param DivisionServiceProduct[] $products
     * @param DivisionServiceInsuranceCompany[] $insuranceCompanies
     *
     * @return DivisionService
     * @throws \Exception
     */
    public function update(int $id, ServiceUpdateForm $form, $products, $insuranceCompanies)
    {
        $service = $this->services->find($id);
        $service->attributes = $form->attributes;

        $categories = $this->getCategories($form->category_ids);
        $divisions = $this->getDivisions($form->division_ids);
        $staff = $this->getStaff($form->staff);

        $oldIDs = ArrayHelper::map($service->products, 'id', 'id');
        $productsToDelete = array_diff($oldIDs, array_filter(ArrayHelper::map($products, 'id', 'id')));

        $oldInsuranceCompanyIDs = ArrayHelper::map($service->insuranceCompanies, 'id', 'id');
        $insuranceCompaniesToDelete = array_diff($oldInsuranceCompanyIDs,
            array_filter(ArrayHelper::map($insuranceCompanies, 'id', 'id')));

        $this->transactionManager->execute(function () use (
            $service,
            $categories,
            $staff,
            $products,
            $productsToDelete,
            $divisions,
            $insuranceCompaniesToDelete,
            $insuranceCompanies
        ) {

            $this->services->edit($service);

            if (!empty($productsToDelete)) {
                $this->products->batchDelete($productsToDelete);
            }

            if (!empty($insuranceCompaniesToDelete)) {
                    $this->insuranceCompanies->batchDelete($insuranceCompaniesToDelete);
            }

            foreach ($products as $product) {
                $service->link('products', $product);
            }

            foreach ($insuranceCompanies as $insuranceCompany) {
                $service->link('insuranceCompanies', $insuranceCompany);
            }

            $this->saveCategories($service, $categories);
            $this->saveDivisions($service, $divisions);
            $this->saveStaff($service, $staff);
        });

        return $service;
    }

    /**
     * @param int $id
     *
     * @return DivisionService
     * @throws \Exception
     */
    public function delete(int $id)
    {
        $service = $this->services->find($id);
        $service->setDeleted();
        $this->transactionManager->execute(function () use ($service) {
            $this->services->edit($service);
        });
        return $service;
    }

    /**
     * @param int[] category_ids
     * @return ServiceCategory[]|array
     */
    private function getCategories(array $category_ids = [])
    {
        return array_map(function (int $category_id) {
            return $this->categories->find($category_id);
        }, $category_ids);
    }

    /**
     * @param int[] $staff
     * @return Staff[]|array
     */
    private function getStaff(array $staff = [])
    {
        return array_map(function (int $staff_id) {
            return $this->employees->find($staff_id);
        }, $staff);
    }

    /**
     * @param DivisionService $service
     * @param ServiceCategory[] $categories
     */
    private function saveCategories(DivisionService $service, array $categories = [])
    {
        $service->unlinkAll('categories', true);
        foreach ($categories as $category) {
            $service->link('categories', $category);
        }
    }

    /**
     * @param DivisionService $service
     * @param Division[] $divisions
     */
    private function saveDivisions(DivisionService $service, array $divisions)
    {
        $service->unlinkAll('divisions', true);
        foreach ($divisions as $division) {
            $service->link('divisions', $division);
        }
    }

    /**
     * @param DivisionService $service
     * @param Staff[] $staff
     */
    private function saveStaff(DivisionService $service, array $staff = [])
    {
        $service->unlinkAll('staffs', true);
        foreach ($staff as $employee) {
            $service->link('staffs', $employee);
        }
    }

    /**
     * @param int $id
     *
     * @return DivisionService
     * @throws \Exception
     */
    public function restore(int $id)
    {
        $service = $this->services->find($id);

        $service->restore();
        $this->transactionManager->execute(function () use ($service) {
            $this->services->edit($service);
        });

        return $service;
    }

    /**
     * @param array $division_ids
     * @return Division[]
     */
    private function getDivisions(array $division_ids)
    {
        return array_map(function (int $division_id) {
            return $this->divisions->find($division_id);
        }, $division_ids);
    }
}
<?php

namespace core\services\staff;

use core\forms\staff\StaffCreateForm;
use core\forms\staff\StaffUpdateForm;
use core\helpers\user\UserHelper;
use core\models\company\Company;
use core\models\company\CompanyPosition;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\Image;
use core\models\rbac\AuthAssignment;
use core\models\Staff;
use core\models\user\User;
use core\models\user\UserDivision;
use core\repositories\company\CompanyPositionRepository;
use core\repositories\company\CompanyRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\DivisionServiceRepository;
use core\repositories\exceptions\NotFoundException;
use core\repositories\rbac\AuthAssignmentRepository;
use core\repositories\StaffRepository;
use core\repositories\user\UserDivisionRepository;
use core\repositories\user\UserRepository;
use core\services\TransactionManager;

class StaffModelService
{
    private $staffs;
    private $companyPositions;
    private $transaction;
    private $divisions;
    private $divisionServices;
    private $users;
    private $companies;
    private $authAssignments;
    private $userDivisions;

    /**
     * StaffModelService constructor.
     *
     * @param StaffRepository           $staffs
     * @param CompanyPositionRepository $companyPositions
     * @param DivisionRepository        $divisions
     * @param DivisionServiceRepository $divisionServices
     * @param UserRepository            $users
     * @param CompanyRepository         $companies
     * @param AuthAssignmentRepository  $authAssignments
     * @param UserDivisionRepository    $userDivisions
     * @param TransactionManager        $transaction
     */
    public function __construct(
        StaffRepository $staffs,
        CompanyPositionRepository $companyPositions,
        DivisionRepository $divisions,
        DivisionServiceRepository $divisionServices,
        UserRepository $users,
        CompanyRepository $companies,
        AuthAssignmentRepository $authAssignments,
        UserDivisionRepository $userDivisions,
        TransactionManager $transaction
    ) {
        $this->staffs           = $staffs;
        $this->companyPositions = $companyPositions;
        $this->transaction      = $transaction;
        $this->divisions        = $divisions;
        $this->divisionServices = $divisionServices;
        $this->users            = $users;
        $this->companies        = $companies;
        $this->authAssignments  = $authAssignments;
        $this->userDivisions    = $userDivisions;
    }

    /**
     * @param integer         $company_id
     * @param StaffCreateForm $form
     * @param Image|null      $image
     *
     * @return Staff
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function hire($company_id, StaffCreateForm $form, $image): Staff
    {
        $company = $this->companies->find($company_id);

        $model = Staff::add(
            $form->name,
            $form->surname,
            $form->phone,
            $form->description,
            $form->description_private,
            $form->gender,
            $form->birth_date,
            null,
            $form->has_calendar,
            $form->color,
            $form->code_1c
        );

        if ( ! empty($image)) {
            $model->setAvatar($image);
        }

        $model->setStaffDivisions($this->getStaffDivisions($form->division_ids));
        $model->setStaffServices($this->getDivisionServices($form->division_service_ids));
        $model->setWorkingPositions($this->getWorkingPositions($form->company_position_ids));

        /* @var AuthAssignment[] $permissions */
        /* @var UserDivision[] $user_divisions */
        $permissions = $user_divisions = [];
        if ($form->create_user) {
            $user = $this->getUser($company, $form->username);
            $user->populateRelation('staff', $model);
            $model->grantSystemAccess(
                $user,
                $form->see_own_orders,
                $form->can_create_order,
                $form->see_customer_phones,
                $form->can_update_order
            );
            $permissions    = AuthAssignment::getStaffAccesses(
                $user,
                $form->user_permissions
            );
            $user_divisions = $this->getUserDivisions(
                $model,
                $form->user_divisions
            );
        }

        $this->transaction->execute(function () use (
            $model,
            $permissions,
            $user_divisions
        ) {
            if ($model->user) {
                if ($model->user->isNewRecord) {
                    $this->users->add($model->user);
                } else {
                    $this->users->edit($model->user);
                }
            }
            $this->staffs->save($model);

            if ($model->hasUserPermissions()) {
                $this->authAssignments->clearPermissions($model->user_id);
                foreach ($permissions as $permission) {
                    $this->authAssignments->save($permission);
                }

                $this->userDivisions->clearStaff($model);
                foreach ($user_divisions as $user_division) {
                    $this->userDivisions->save($user_division);
                }
                UserHelper::invalidateMainMenuCache($model->user_id);
            }
        });

        return $model;
    }

    /**
     * @param integer         $staff_id
     * @param integer         $company_id
     * @param StaffUpdateForm $form
     * @param Image|null      $image
     *
     * @return Staff
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function edit(
        $staff_id,
        $company_id,
        StaffUpdateForm $form,
        $image
    ): Staff {
        $model   = $this->staffs->find($staff_id);
        $company = $this->companies->find($company_id);

        $model->edit(
            $form->name,
            $form->surname,
            $form->description,
            $form->description_private,
            $form->gender,
            $form->birth_date,
            null,
            $form->has_calendar,
            $form->color,
            $form->code_1c
        );

        if ( ! empty($image)) {
            $model->setAvatar($image);
        }

        if ($form->phone !== $model->phone) {
            $model->changePhone($form->phone);
        }

        $model->setStaffDivisions($this->getStaffDivisions($form->division_ids));
        $model->setStaffServices($this->getDivisionServices($form->division_service_ids));
        $model->setWorkingPositions($this->getWorkingPositions($form->company_position_ids));

        /* @var AuthAssignment[] $permissions */
        /* @var UserDivision[] $user_divisions */
        $permissions = $user_divisions = [];
        if ($form->create_user) {

            if ($model->hasUserPermissions() && $model->user->username === $form->username) {
                $user = $this->users->find($model->user_id);
            } else {
                $user = $this->getUser($company, $form->username);
            }

            $user->populateRelation('staff', $model);

            $model->grantSystemAccess(
                $user,
                $form->see_own_orders,
                $form->can_create_order,
                $form->see_customer_phones,
                $form->can_update_order
            );
            $permissions    = AuthAssignment::getStaffAccesses(
                $user,
                $form->user_permissions
            );
            $user_divisions = $this->getUserDivisions(
                $model,
                $form->user_divisions
            );
        } else {
            $model->removeUserPermissions();
        }

        $this->transaction->execute(function () use (
            $model,
            $permissions,
            $user_divisions
        ) {
            if ($model->user) {
                if ($model->user->isNewRecord) {
                    $this->users->add($model->user);
                } else {
                    $this->users->edit($model->user);
                }

                if( ! $model->hasUserPermissions() ){
                    $this->authAssignments->clearPermissions($model->user->id);
                }
            }
            $this->staffs->save($model);

            if ($model->hasUserPermissions()) {
                $this->authAssignments->clearPermissions($model->user_id);
                foreach ($permissions as $permission) {
                    $this->authAssignments->save($permission);
                }

                $this->userDivisions->clearStaff($model);
                foreach ($user_divisions as $user_division) {
                    $this->userDivisions->save($user_division);
                }
                UserHelper::invalidateMainMenuCache($model->user_id);
            }
        });

        return $model;
    }

    /**
     * @param int $id
     * @param int $company_id
     *
     * @return Staff
     * @throws \Exception
     */
    public function restore(int $id, int $company_id)
    {
        $staff = $this->staffs->find($id);
        $company = $this->companies->find($company_id);

        if ($staff->user_id) {
            $this->guardStaffQuantity($company);
        }

        $staff->restore();
        $this->transaction->execute(function () use ($staff) {
            $this->staffs->save($staff);
        });

        return $staff;
    }

    /**
     * @param array $division_ids
     *
     * @return Division[]
     */
    private function getStaffDivisions(array $division_ids)
    {
        if (empty($division_ids)) {
            throw new \DomainException('Empty staff divisions');
        }

        return array_map(function (int $division_id) {
            return $this->divisions->find($division_id);
        }, $division_ids);
    }

    /**
     * @param array $division_service_ids
     *
     * @return DivisionService[]
     */
    private function getDivisionServices(array $division_service_ids)
    {
        return array_map(function (int $division_service_id) {
            return $this->divisionServices->find($division_service_id);
        }, $division_service_ids);
    }

    /**
     * @param array $company_position_ids
     *
     * @return CompanyPosition[]
     */
    private function getWorkingPositions(array $company_position_ids)
    {
        return array_map(function (int $company_position_id) {
            return $this->companyPositions->find($company_position_id);
        }, $company_position_ids);
    }

    /**
     * @param Staff $staff
     * @param array $user_divisions
     *
     * @return UserDivision[]
     */
    private function getUserDivisions(Staff $staff, $user_divisions)
    {
        if ( ! is_array($user_divisions)) {
            throw new \DomainException(\Yii::t('app', 'User divisions should not be empty'));
        }

        return array_map(function (int $division_id) use ($staff) {
            $division = $this->divisions->find($division_id);

            return UserDivision::add($division, $staff);
        }, $user_divisions);
    }

    /**
     * @param Company $company
     * @param string  $phone
     *
     * @return User
     * @throws \yii\base\Exception
     */
    private function getUser(Company $company, $phone)
    {
        if (empty($phone)) {
            throw new \DomainException('Phone is required');
        }

        try {
            $user = $this->users->findByPhone($phone);

            if ($user->isDisabled()) {
                $user->company_id = $company->id;
            }

            if ($user->company_id !== $company->id) {
                throw new \DomainException('Staff has other company access');
            }

            $user->enable();

            $this->guardStaffNotExists($user->id);
        } catch (NotFoundException $e) {

            $this->guardStaffQuantity($company);

            $user = User::add(
                $company->id,
                $phone,
                null,
                null
            );
        }

        return $user;
    }

    /**
     * Returns exception if staff exists
     *
     * @param integer $user_id
     */
    private function guardStaffNotExists(int $user_id)
    {
        try {
            $this->staffs->findByUser($user_id);
            throw new \DomainException('Staff already has system accesses');
        } catch (NotFoundException $e) {
        }
    }

    /**
     * @param Company $company
     */
    private function guardStaffQuantity(Company $company)
    {
        if (!$company->hasFreeStaffSlots()) {
            throw new \DomainException("Достигнут лимит количества сотрудников.");
        }
    }

    /**
     * @param int $id
     *
     * @return Staff
     * @throws \Exception
     */
    public function fire(int $id)
    {
        $model = $this->staffs->find($id);

        if ($model->hasForthcomingOrders()) {
            $dates = implode(
                '<br>',
                array_map(
                    function ($order_date) {
                        return \Yii::$app->formatter->asDatetime($order_date);
                    },
                    $model->getForthcomingOrders()->select('datetime')->column()
                )
            );
            throw new \DomainException("У сотрудника имеются предстоящие записи на следующие даты: <br>".$dates);
        }

        $this->transaction->execute(function () use ($model) {
            if ($model->hasUserPermissions()) {
                $this->authAssignments->clearPermissions($model->user_id);
            }
            $model->fire();
        });

        return $model;
    }

    /**
     * @param int   $id
     * @param array $services
     *
     * @return array
     * @throws \Exception
     */
    public function addServices(int $id, array $services)
    {
        $model = $this->staffs->find($id);

        $oldServices = array_map(function (DivisionService $divisionService) {
            return $divisionService->id;
        }, $model->divisionServices);
        $services = array_merge($oldServices, $services);

        $model->setStaffServices($this->getDivisionServices($services));

        $this->transaction->execute(function () use ($model) {
            $this->staffs->save($model);
        });

        return $services;
    }

    /**
     * @param int   $id
     * @param array $services
     *
     * @return array
     * @throws \Exception
     */
    public function deleteServices(int $id, array $services)
    {
        $model = $this->staffs->find($id);

        $oldServices = array_map(function (DivisionService $divisionService) {
            return $divisionService->id;
        }, $model->divisionServices);
        $services = array_diff($oldServices, $services);

        $model->setStaffServices($this->getDivisionServices($services));

        $this->transaction->execute(function () use ($model) {
            $this->staffs->save($model);
        });

        return $services;
    }
}
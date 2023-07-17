<?php

namespace core\services\medCard;

use core\models\company\Company;
use core\models\medCard\MedCard;
use core\models\medCard\MedCardCompanyComment;
use core\models\medCard\MedCardTab;
use core\models\medCard\MedCardTabComment;
use core\models\medCard\MedCardTabService;
use core\models\medCard\MedCardTooth;
use core\models\user\User;
use core\repositories\CommentTemplateCategoryRepository;
use core\repositories\company\CompanyRepository;
use core\repositories\DivisionServiceRepository;
use core\repositories\exceptions\AlreadyExistsException;
use core\repositories\exceptions\EmptyVariableException;
use core\repositories\exceptions\NotFoundException;
use core\repositories\medCard\MedCardCommentCategoryRepository;
use core\repositories\medCard\MedCardCommentRepository;
use core\repositories\medCard\MedCardCompanyCommentRepository;
use core\repositories\medCard\MedCardRepository;
use core\repositories\medCard\MedCardTabCommentRepository;
use core\repositories\medCard\MedCardTabServiceRepository;
use core\repositories\medCard\MedCardToothDiagnosisRepository;
use core\repositories\medCard\MedCardToothRepository;
use core\repositories\order\OrderRepository;
use core\repositories\user\UserRepository;
use core\services\medCard\dto\MedCardTabCommentData;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ToothData;
use core\services\TransactionManager;

class MedCardModelService
{
    private $commentTemplateCategoryRepository;
    private $medCardRepository;
    private $medCardTabCommentRepository;
    private $orderRepository;
    private $toothRepository;
    private $transactionManager;
    private $divisionServiceRepository;
    private $userRepository;
    private $medCardTabServiceRepository;
    private $companyRepository;
    private $medCardCommentRepository;
    private $medCardCompanyCommentRepository;
    private $medCardToothDiagnosisRepository;
    private $medCardCommentCategoryRepository;

    /**
     * MedCardService constructor.
     *
     * @param CommentTemplateCategoryRepository $commentTemplateCategoryRepository
     * @param MedCardRepository                 $medCardRepository
     * @param MedCardTabCommentRepository       $medCardTabCommentRepository
     * @param OrderRepository                   $orderRepository
     * @param MedCardToothRepository            $toothRepository
     * @param DivisionServiceRepository         $divisionServiceRepository
     * @param UserRepository                    $userRepository
     * @param CompanyRepository                 $companyRepository
     * @param MedCardCommentRepository          $medCardCommentRepository
     * @param MedCardCompanyCommentRepository   $medCardCompanyCommentRepository
     * @param MedCardTabServiceRepository       $medCardTabServiceRepository
     * @param MedCardToothDiagnosisRepository   $medCardToothDiagnosisRepository
     * @param MedCardCommentCategoryRepository  $medCardCommentCategoryRepository
     * @param TransactionManager                $transactionManager
     */
    public function __construct(
        CommentTemplateCategoryRepository $commentTemplateCategoryRepository,
        MedCardRepository $medCardRepository,
        MedCardTabCommentRepository $medCardTabCommentRepository,
        OrderRepository $orderRepository,
        MedCardToothRepository $toothRepository,
        DivisionServiceRepository $divisionServiceRepository,
        UserRepository $userRepository,
        CompanyRepository $companyRepository,
        MedCardCommentRepository $medCardCommentRepository,
        MedCardCompanyCommentRepository $medCardCompanyCommentRepository,
        MedCardTabServiceRepository $medCardTabServiceRepository,
        MedCardToothDiagnosisRepository $medCardToothDiagnosisRepository,
        MedCardCommentCategoryRepository $medCardCommentCategoryRepository,
        TransactionManager $transactionManager
    ) {
        $this->commentTemplateCategoryRepository
                                           = $commentTemplateCategoryRepository;
        $this->medCardRepository           = $medCardRepository;
        $this->medCardTabCommentRepository = $medCardTabCommentRepository;
        $this->orderRepository             = $orderRepository;
        $this->toothRepository             = $toothRepository;
        $this->transactionManager          = $transactionManager;
        $this->divisionServiceRepository   = $divisionServiceRepository;
        $this->userRepository              = $userRepository;
        $this->medCardTabServiceRepository = $medCardTabServiceRepository;
        $this->companyRepository           = $companyRepository;
        $this->medCardCommentRepository    = $medCardCommentRepository;
        $this->medCardCompanyCommentRepository
                                           = $medCardCompanyCommentRepository;
        $this->medCardToothDiagnosisRepository
                                           = $medCardToothDiagnosisRepository;
        $this->medCardCommentCategoryRepository
                                           = $medCardCommentCategoryRepository;
    }

    /**
     * @param integer $created_user_id
     * @param integer $company_id
     * @param integer $order_id
     * @param array   $orderTeethData
     * @param array   $medCardTabCommentsData
     * @param array   $servicesData
     * @param integer $diagnosis_id
     *
     * @return MedCardTab
     * @throws \Exception
     */
    public function createTab(
        $created_user_id,
        $company_id,
        $order_id,
        $orderTeethData,
        $medCardTabCommentsData,
        $servicesData,
        $diagnosis_id
    ) {
        $order       = $this->orderRepository->find($order_id);
        $createdUser = $this->userRepository->find($created_user_id);
        $company     = $this->companyRepository->find($company_id);

        try {
            $medCard = $this->medCardRepository->findByOrder($order_id);
        } catch (NotFoundException $e) {
            $medCard = MedCard::add($order->id);
        }

        $medCardTab = MedCardTab::add($medCard, $diagnosis_id);

        $orderTeeth    = $this->getOrderTeeth($orderTeethData, $medCardTab);
        $medCardTabComments = $this->getMedCardTabComments(
            $medCardTabCommentsData,
            $medCardTab
        );
        $services      = $this->getServices(
            $servicesData,
            $medCardTab,
            $createdUser
        );

        $newComments = $this->getNewComments($company, $medCardTabComments);

        $this->transactionManager->execute(function () use (
            $medCard,
            $medCardTab,
            $orderTeeth,
            $medCardTabComments,
            $services,
            $newComments
        ) {
            // Med Card
            if ($medCard->isNewRecord) {
                $this->medCardRepository->add($medCard);
            }
            // Med Card Tab
            $this->medCardRepository->add($medCardTab);
            // Teeth
            $this->insertOrderTeeth($orderTeeth, $medCardTab);
            // Comments
            $this->saveMedCardTabComments($medCardTabComments);
            // Services
            $this->saveServices($services);
            // New Comments
            foreach ($newComments as $newComment) {
                $this->medCardCompanyCommentRepository->save($newComment);
            }
        });

        return $medCardTab;
    }

    /**
     * @param integer $created_user_id
     * @param integer $company_id
     * @param integer $med_card_tab_id
     * @param array   $orderTeethData
     * @param array   $medCardTabCommentsData
     * @param array   $servicesData
     * @param integer $diagnosis_id
     *
     * @return MedCardTab
     * @throws \Exception
     */
    public function editTab(
        $created_user_id,
        $company_id,
        $med_card_tab_id,
        $orderTeethData,
        $medCardTabCommentsData,
        $servicesData,
        $diagnosis_id
    ) {
        $medCardTab  = $this->medCardRepository->findTab($med_card_tab_id);
        $createdUser = $this->userRepository->find($created_user_id);
        $company     = $this->companyRepository->find($company_id);

        $medCardTab->diagnosis_id = $diagnosis_id;

        $orderTeeth    = $this->getOrderTeeth($orderTeethData, $medCardTab);
        $medCardTabComments = $this->getMedCardTabComments(
            $medCardTabCommentsData,
            $medCardTab
        );
        $services      = $this->getServices(
            $servicesData,
            $medCardTab,
            $createdUser
        );

        $newComments = $this->getNewComments($company, $medCardTabComments);

        $this->transactionManager->execute(function () use (
            $medCardTab,
            $orderTeeth,
            $medCardTabComments,
            $services,
            $newComments
        ) {
            $this->toothRepository->deleteAll($medCardTab->id);
            // Teeth
            $this->insertOrderTeeth($orderTeeth, $medCardTab);
            // Comments
            $this->saveMedCardTabComments($medCardTabComments);
            // Services
            $this->saveServices($services);
            // New Comments
            foreach ($newComments as $newComment) {
                $this->medCardCompanyCommentRepository->save($newComment);
            }
        });

        return $medCardTab;
    }

    /**
     * @param $med_card_tab_id
     *
     * @throws \Exception
     */
    public function deleteTab($med_card_tab_id)
    {
        $medCardTab = $this->medCardRepository->findTab($med_card_tab_id);

        $this->transactionManager->execute(function () use ($medCardTab) {
            $this->medCardTabCommentRepository->deleteAll($medCardTab->id);
            $this->toothRepository->deleteAll($medCardTab->id);
            $this->medCardTabServiceRepository->deleteAll($medCardTab->id);
            $this->medCardRepository->delete($medCardTab);
        });
    }

    /**
     * Save order comments
     *
     * @param MedCardTabComment[] $medCardTabComments
     */
    private function saveMedCardTabComments($medCardTabComments)
    {
        foreach ($medCardTabComments as $medCardTabComment) {
            if ($medCardTabComment->isNewRecord) {
                $this->medCardTabCommentRepository->add($medCardTabComment);
            } else {
                $this->medCardTabCommentRepository->edit($medCardTabComment);
            }
        }
    }

    /**
     * Save order comments
     *
     * @param MedCardTabService[] $services
     */
    private function saveServices($services)
    {
        foreach ($services as $service) {
            if ($service->isNewRecord) {
                $this->medCardTabServiceRepository->add($service);
            } else {
                $this->medCardTabServiceRepository->edit($service);
            }
        }
    }

    /**
     * Inserts MedCardTooth models
     *
     * @param MedCardTooth[] $orderTeeth
     * @param MedCardTab     $medCardTab
     */
    private function insertOrderTeeth($orderTeeth, MedCardTab $medCardTab)
    {
        foreach ($orderTeeth as $MedCardTooth) {
            if ($this->toothRepository->isTeethInMedCard(
                $medCardTab->med_card_id,
                $MedCardTooth->teeth_num)
            ) {
                throw new \DomainException('На зуб уже заполнен диагноз. Необходимо сначала удалить зуб.');
            }
            $this->toothRepository->add($MedCardTooth);
        }
    }

    /**
     * Returns MedCardTabComment models found by id or returns new
     *
     * @param MedCardTabCommentData[] $medCardTabCommentsData
     * @param MedCardTab              $medCardTab
     *
     * @return MedCardTabComment[]
     */
    private function getMedCardTabComments(
        $medCardTabCommentsData,
        MedCardTab $medCardTab
    ) {
        if ($medCardTab->isNewRecord) {
            return array_map(function (MedCardTabCommentData $data) use ($medCardTab) {
                $category
                    = $this->commentTemplateCategoryRepository->find($data->comment_template_category_id);

                return MedCardTabComment::add(
                    $medCardTab,
                    $category,
                    $data->comment
                );
            }, $medCardTabCommentsData);
        }

        $medCardTabComments = [];
        foreach ($medCardTabCommentsData as $data) {
            try {
                $medCardTabComment
                    = $this->medCardTabCommentRepository->findByMedCardTabAndCategory(
                    $medCardTab->id,
                    $data->comment_template_category_id
                );
                $medCardTabComment->changeComment($data->comment);
            } catch (NotFoundException $e) {
                $category
                              = $this->commentTemplateCategoryRepository->find($data->comment_template_category_id);
                $medCardTabComment = MedCardTabComment::add($medCardTab, $category,
                    $data->comment);
            }
            $medCardTabComments[] = $medCardTabComment;
        }

        return $medCardTabComments;
    }

    /**
     * Returns new MedCardTooth models
     *
     * @param ToothData[] $orderTeethData
     * @param MedCardTab  $medCardTab
     *
     * @return MedCardTooth[]
     */
    private function getOrderTeeth($orderTeethData, MedCardTab $medCardTab)
    {
        return array_map(function (ToothData $data) use ($medCardTab) {
            $diagnosis = $this->medCardToothDiagnosisRepository->find($data->diagnosis_id);
            return MedCardTooth::add(
                $medCardTab,
                $diagnosis,
                $data->number,
                $data->type,
                $data->mobility
            );
        }, $orderTeethData);
    }

    /**
     * Returns new MedCardTooth models
     *
     * @param OrderServiceData[] $serviceData
     * @param MedCardTab         $medCardTab
     * @param User               $createdUser
     *
     * @return MedCardTabService[]
     */
    private function getServices(
        $serviceData,
        MedCardTab $medCardTab,
        User $createdUser
    ) {
        $services
            = $this->medCardTabServiceRepository->findAllByMedCardTab($medCardTab->id);
        foreach ($services as $service) {
            $service->setDeleted();
        }

        foreach ($serviceData as $data) {
            if (isset($services[$data->division_service_id])) {
                $services[$data->division_service_id]->revertDeleted();
                $services[$data->division_service_id]->edit(
                    $data->quantity,
                    $data->discount,
                    $data->price
                );
            } else {
                $divisionService
                    = $this->divisionServiceRepository->find($data->division_service_id);

                $services[$data->division_service_id] = MedCardTabService::add(
                    $medCardTab,
                    $divisionService,
                    $createdUser,
                    $data->quantity,
                    $data->discount,
                    $data->price
                );
            }
        }

        return $services;
    }

    /**
     * @param Company        $company
     * @param MedCardTabComment[] $comments
     *
     * @return MedCardCompanyComment[]
     */
    private function getNewComments(Company $company, $comments)
    {
        $newComments = [];
        foreach ($comments as $comment) {
            $new_comments = $this->filterNewComments($company, $comment);

            $category = $this->medCardCommentCategoryRepository->find($comment->category->id);
            foreach ($new_comments as $comment_text) {
                try {
                    $newComment = MedCardCompanyComment::add(
                        $company,
                        $category,
                        trim($comment_text)
                    );
                    array_push($newComments, $newComment);
                } catch (AlreadyExistsException $e) {

                } catch (EmptyVariableException $e) {

                }
            }
        }
        return $newComments;
    }

    /**
     * @param Company      $company
     * @param MedCardTabComment $medCardTabComment
     *
     * @return string[]
     */
    private function filterNewComments(
        Company $company,
        MedCardTabComment $medCardTabComment
    ) {
        $comments_list = explode(
            '; ',
            preg_replace(
                MedCardCompanyComment::HEADLINE_PATTERN,
                '',
                $medCardTabComment->comment
            )
        );

        return array_filter(
            $comments_list,
            function ($comment) use ($medCardTabComment, $company) {
                $comment = trim($comment);
                if (empty($comment)) {
                    return false;
                }

                try {
                    $this->medCardCommentRepository->findByCommentAndCategory(
                        $comment,
                        $medCardTabComment->category_id
                    );

                    return false;
                } catch (NotFoundException $e) {
                }

                try {
                    $this->medCardCompanyCommentRepository->findByCompanyCategoryComment(
                        $company->id,
                        $medCardTabComment->category_id,
                        $comment
                    );

                    return false;
                } catch (NotFoundException $e) {
                }

                return true;
            }
        );
    }
}
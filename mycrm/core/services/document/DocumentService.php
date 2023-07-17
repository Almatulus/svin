<?php

namespace core\services\document;

use core\forms\document\DocumentCreateForm;
use core\forms\document\DocumentUpdateForm;
use core\helpers\document\DocumentHelper;
use core\helpers\GenderHelper;
use core\models\company\Company;
use core\models\document\DentalCardElement;
use core\models\document\Document;
use core\models\document\DocumentService as Service;
use core\models\document\DocumentValue;
use core\models\medCard\MedCardCompanyComment;
use core\repositories\exceptions\AlreadyExistsException;
use core\repositories\exceptions\EmptyVariableException;
use core\repositories\exceptions\NotFoundException;
use core\repositories\medCard\MedCardCommentCategoryRepository;
use core\repositories\medCard\MedCardCommentRepository;
use core\repositories\medCard\MedCardCompanyCommentRepository;
use PhpOffice\PhpWord\IOFactory;
use Yii;

class DocumentService
{
    protected $documentHelper;

    private $medCardCommentCategoryRepository;
    private $medCardCompanyCommentRepository;
    private $medCardCommentRepository;

    /**
     * DocumentHelper constructor.
     * @param DocumentHelper $documentHelper
     * @param MedCardCommentCategoryRepository $medCardCommentCategoryRepository
     * @param MedCardCompanyCommentRepository $medCardCompanyCommentRepository
     * @param MedCardCommentRepository $medCardCommentRepository
     */
    public function __construct(
        DocumentHelper $documentHelper,
        MedCardCommentCategoryRepository $medCardCommentCategoryRepository,
        MedCardCompanyCommentRepository $medCardCompanyCommentRepository,
        MedCardCommentRepository $medCardCommentRepository
    ) {
        $this->documentHelper = $documentHelper;
        $this->medCardCommentCategoryRepository = $medCardCommentCategoryRepository;
        $this->medCardCompanyCommentRepository = $medCardCompanyCommentRepository;
        $this->medCardCommentRepository = $medCardCommentRepository;
    }

    /**
     * @param DocumentCreateForm $form
     *
     * @return Document
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function create(DocumentCreateForm $form): Document
    {
        /** @var Document $document */
        $document = new Document([
            'document_form_id'    => $form->getId(),
            'company_customer_id' => $form->customer_id,
            'manager_id'          => $form->manager_id,
            'staff_id'            => $form->staff_id,
        ]);

        $tr = Yii::$app->db->beginTransaction();

        $document->save(false);

        if ($form->getHasServices() && !empty($form->services)) {
            foreach ($form->services as $element_id => $service) {
                $documentService = Yii::createObject([
                    'class'       => Service::class,
                    'document_id' => $document->id,
                    'service_id'  => $service['service_id'],
                    'price'       => $service['price'],
                    'quantity'    => $service['quantity'],
                    'discount'    => $service['discount']
                ]);
                $documentService->save(false);
            }
        }

        foreach ($form->getValues() as $element_id => $value) {
            if (empty($value)) {
                continue;
            }
            $documentValue = Yii::createObject([
                'class'                    => DocumentValue::class,
                'document_id'              => $document->id,
                'document_form_element_id' => $element_id,
                'value'                    => $value
            ]);
            $documentValue->save(false);

            if ($documentValue->documentFormElement->is_comment) {
                $this->saveComment(
                    $document->companyCustomer->company,
                    $documentValue->documentFormElement->key,
                    $value
                );
            }
        }

        if ($form->getHasDentalCard() && !empty($form->dentalCard)) {
            foreach ($form->dentalCard as $tooth) {
                $dentalCardElement = Yii::createObject([
                    'class'        => DentalCardElement::class,
                    'document_id'  => $document->id,
                    'number'       => $tooth['number'],
                    'diagnosis_id' => $tooth['diagnosis_id'],
                    'mobility'     => $tooth['mobility'] ?? null,
                ]);
                $dentalCardElement->save(false);
            }
        }

        $tr->commit();

        $document->refresh();

        return $document;
    }

    /**
     * @param Document $document
     * @param DocumentUpdateForm $form
     *
     * @return Document
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function update(Document $document, DocumentUpdateForm $form): Document
    {
        $document->setAttributes([
            'manager_id' => $form->manager_id,
            'staff_id'   => $form->staff_id
        ]);

        $tr = Yii::$app->db->beginTransaction();

        $document->save(false);

        DocumentValue::deleteAll(['document_id' => $document->id]);
        foreach ($form->getValues() as $element_id => $value) {
            if (!isset($value)) {
                continue;
            }
            $documentValue = Yii::createObject([
                'class'                    => DocumentValue::class,
                'document_id'              => $document->id,
                'document_form_element_id' => $element_id,
                'value'                    => $value
            ]);
            $documentValue->save(false);

            if ($documentValue->documentFormElement->is_comment) {
                $this->saveComment(
                    $document->companyCustomer->company,
                    $documentValue->documentFormElement->key,
                    $value
                );
            }
        }

        Service::deleteAll(['document_id' => $document->id]);
        if ($form->getHasServices() && !empty($form->services)) {
            foreach ($form->services as $element_id => $service) {
                $documentService = Yii::createObject([
                    'class'       => Service::class,
                    'document_id' => $document->id,
                    'service_id'  => $service['service_id'],
                    'price'       => $service['price'],
                    'quantity'    => $service['quantity'],
                    'discount'    => $service['discount']
                ]);
                $documentService->save(false);
            }
        }

        DentalCardElement::deleteAll(['document_id' => $document->id]);
        if ($form->getHasDentalCard()) {
            foreach ($form->dentalCard as $tooth) {
                $dentalCardElement = Yii::createObject([
                    'class'        => DentalCardElement::class,
                    'document_id'  => $document->id,
                    'number'       => $tooth['number'],
                    'diagnosis_id' => $tooth['diagnosis_id'],
                    'mobility'     => $tooth['mobility'] ?? null
                ]);
                $dentalCardElement->save(false);
            }
        }

        $tr->commit();

        $document->refresh();

        return $document;
    }

    /**
     * @param Document $document
     *
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function generate(Document $document)
    {
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(Yii::$app->basePath . "/.." . $document->documentForm->doc_path);

        if ($document->documentForm->has_services) {
            $services = $document->services;

            if (sizeof($services) == 0) {
                $docPath = $document->documentForm->doc_path;
                $docPath = str_replace('.doc', '_noservice.doc', $docPath);
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(Yii::$app->basePath . "/.." . $docPath);
            } else {
                $templateProcessor->cloneRow('serviceName', sizeof($services) ?: 1);

                $i = 0;
                $totalPrice = 0;
                foreach ($services as $service) {
                    $i++;
                    $servicePrice = $service->getTotalPrice();
                    $totalPrice += $servicePrice;
                    $templateProcessor->setValue('serviceName#'.$i, $service->service->getFullName());
                    $templateProcessor->setValue('serviceQuantity#'.$i, Yii::$app->formatter->asDecimal($service->quantity));
                    $templateProcessor->setValue('serviceDiscount#'.$i, Yii::$app->formatter->asDecimal($service->discount));
                    $templateProcessor->setValue('servicePrice#'.$i, Yii::$app->formatter->asDecimal($servicePrice));
                }

                $templateProcessor->setValue('totalPrice', Yii::$app->formatter->asDecimal($totalPrice));
            }
        }

        $this->documentHelper->searchPatterns = [
            'created_at',
            'staff_full_name',
            'customer_full_name',
            'customer_phone',
            'customer_address',
            'customer_birth_date',
            'customer_gender',
            'customer_employer',
            'customer_age',
            'company_phone',
            'company_name',
        ];
        $companyCustomer = $document->companyCustomer;
        $customer = $companyCustomer->customer;
        $company = $companyCustomer->company;
        $this->documentHelper->values = [
            Yii::$app->formatter->asDate($document->created_at),
            $document->staff->getFullName(),
            $customer->getFullName(),
            $customer->phone,
            $companyCustomer->address,
            Yii::$app->formatter->asDate($customer->birth_date),
            GenderHelper::getGenderLabel($customer->gender),
            $companyCustomer->employer,
            $customer->getAge(),
            $company->phone,
            $company->name
        ];

        $this->documentHelper->generateSearchPatterns($document);

        $templateProcessor->setValue(
            $this->documentHelper->searchPatterns,
            $this->documentHelper->values
        );

        $phpWord = IOFactory::load($templateProcessor->save());
        $xmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        header('Access-Control-Allow-Origin: *');

        ob_start();
        $xmlWriter->save("php://output");
        return ob_get_contents();
    }

    /**
     * @param Company $company
     * @param int $category_id
     * @param string $comment
     */
    public function saveComment(Company $company, int $category_id, string $comment)
    {
        $new_comments = $this->filterNewComments($company, $category_id, $comment);

        $category = $this->medCardCommentCategoryRepository->find($category_id);

        foreach ($new_comments as $comment_text) {
            try {
                $newComment = MedCardCompanyComment::add(
                    $company,
                    $category,
                    trim($comment_text)
                );
                $this->medCardCompanyCommentRepository->save($newComment);
            } catch (AlreadyExistsException $e) {

            } catch (EmptyVariableException $e) {

            }
        }
    }

    /**
     * @param Company $company
     * @param int $category_id
     * @param string $comment
     * @return string[]
     */
    private function filterNewComments(
        Company $company,
        int $category_id,
        string $comment
    ) {
        $comments_list = explode(
            '; ',
            preg_replace(
                MedCardCompanyComment::HEADLINE_PATTERN,
                '',
                $comment
            )
        );

        return array_filter(
            $comments_list,
            function ($comment) use ($category_id, $company) {
                $comment = trim($comment);
                if (empty($comment)) {
                    return false;
                }

                try {
                    $this->medCardCommentRepository->findByCommentAndCategory(
                        $comment,
                        $category_id
                    );

                    return false;
                } catch (NotFoundException $e) {
                }

                try {
                    $this->medCardCompanyCommentRepository->findByCompanyCategoryComment(
                        $company->id,
                        $category_id,
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
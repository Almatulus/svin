<?php

namespace core\services\document;

use common\components\word\CustomTemplateProcessor;
use core\helpers\AppHelper;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use core\models\document\DocumentFormGroup;
use core\services\TransactionManager;
use frontend\modules\document\forms\ElementForm;
use frontend\modules\document\forms\ElementsForm;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class FormService
{
    protected $transactionManager;

    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param ElementsForm $elementsForm
     *
     * @return array
     * @throws \Exception
     */
    public function createElements(ElementsForm $elementsForm)
    {
        $elements = $this->getElements($elementsForm);

        $this->transactionManager->execute(function () use ($elementsForm, $elements) {

            // ToDo Rewrite
            $elementsForm->getDocumentForm()->save(false);

            $elementIds = array_filter($elementsForm->elements, function (array $element) {
                return isset($element['id']) && is_numeric($element["id"]);
            });
            $elementIds = array_map(function (array $element) {
                return $element['id'];
            }, $elementIds);

            DocumentFormElement::deleteAll([
                "AND",
                ['not in', 'id', $elementIds],
                ['document_form_id' => $elementsForm->getId()],
            ]);

            foreach ($elements as $element) {
                $element->save(false);
            }
        });

        return $elements;
    }

    /**
     * @param ElementsForm $elementsForm
     * @return DocumentFormElement[]
     */
    public function getElements(ElementsForm $elementsForm)
    {
        $result = [];

        foreach ($elementsForm->elements as $elementData) {

            if (isset($elementData['id']) && is_numeric($elementData["id"])) {
                $element = DocumentFormElement::findOne([
                    'id'               => $elementData['id'],
                    'document_form_id' => $elementsForm->getId()
                ]);
            } else {
                $element = new DocumentFormElement();
            }

            $element->setAttributes($elementData);
            $element->document_form_id = $elementsForm->getId();

            if (!empty($elementData['options'])) {
                $element->options = AppHelper::arrayToPg(...$elementData['options']);
            }

            $result[] = $element;
        }

        return $result;
    }

    /**
     * @param DocumentForm $model
     *
     * @return DocumentForm
     * @throws \Exception
     */
    public function duplicate(DocumentForm $model)
    {
        // Copy Form
        $newModel = new DocumentForm();
        $newModel->setAttributes($model->attributes);
        $newModel->name = $newModel->name . ' (COPY)';

        $this->transactionManager->execute(function () use ($model, $newModel) {

            if ( ! $newModel->save()) {
                $errors = $newModel->getErrors();
                throw new \DomainException('Form: ' . reset($errors)[0]);
            }

            foreach ($model->groups as $group) {
                // Copy Group
                $newGroup = new DocumentFormGroup();
                $newGroup->setAttributes($group->attributes);
                $newGroup->document_form_id = $newModel->id;

                if ( ! $newGroup->save()) {
                    $errors = $newGroup->getErrors();
                    throw new \DomainException('Group: ' . reset($errors)[0]);
                }

                foreach ($group->documentFormElements as $documentFormElement) {
                    // Copy elements with group
                    $newElement = new DocumentFormElement();
                    $newElement->setAttributes($documentFormElement->attributes);
                    $newElement->document_form_group_id = $newGroup->id;
                    $newElement->document_form_id = $newModel->id;

                    if ( ! $newElement->save()) {
                        $errors = $newElement->getErrors();
                        throw new \DomainException('Group Element: ' . reset($errors)[0]);
                    }
                }
            }

            $elements = $model->getElements()->andWhere(['document_form_group_id' => null])->all();

            foreach ($elements as $element) {
                // Copy elements with group
                $newElement = new DocumentFormElement();
                $newElement->setAttributes($element->attributes);
                $newElement->document_form_id = $newModel->id;

                if ( ! $newElement->save()) {
                    $errors = $newElement->getErrors();
                    throw new \DomainException('Element: ' . reset($errors)[0]);
                }
            }
        });

        return $newModel;
    }

    /**
     * @return array
     */
    public function getDocumentFormsList()
    {
        return ArrayHelper::map(DocumentForm::find()->enabled()->all(), "id", "name");
    }

    /**
     * @param int $id
     * @param string $path
     * @param string $filename
     * @return DocumentForm
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function import(int $id, string $path, string $filename)
    {
        /** @var DocumentForm $model */
        $model = DocumentForm::findOne($id);

        if (!$model) {
            throw new \InvalidArgumentException("Invalid id");
        }

        $model->doc_path = "/static/doc_templates/{$filename}.docx";
        $model->update(false);

        $newPath = \Yii::$app->basePath . '/..' . $model->doc_path;

        $elementsForm = $this->parseElements($model->id, $path, $newPath);
        // ToDo Consider rewriting forms and service. Make it more flexible.
        // Validate to filter options of elements. See [[ElementsForm::rules()]]
        $elementsForm->validate();

        $this->createElements($elementsForm);

        return $model;
    }

    /**
     * @param int $id
     * @param string $path
     * @param string $newPath
     * @return ElementsForm
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    private function parseElements(int $id, string $path, string $newPath)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("File does not exist.");
        }

        $templateProcessor = new CustomTemplateProcessor($path);

        $elements = [];
        $searchPatterns = [];
        $values = [];

        foreach ($templateProcessor->getVariables() as $key => $variable) {
            $parts = explode("|", $variable);
            if (sizeof($parts) >= 6) {
                list($key, $type, $label, $order, $group_id, $row_id) = $parts;

                $elementData = [
                    'document_form_group_id' => $group_id === 'null' ? null : $group_id,
                    'label'                  => $label,
                    'key'                    => $key,
                    'order'                  => $order === 'null' ? null : $order,
                    'type'                   => $type,
                    'raw_id'                 => $row_id === 'null' ? null : $row_id,
                ];

                if ($type == DocumentFormElement::TYPE_RADIOLIST ||
                    $type == DocumentFormElement::TYPE_RADIOLIST ||
                    $type == DocumentFormElement::TYPE_SELECT) {
                    $elementData['options'] = isset($parts[6])
                        ? (array_map(function ($option) {
                            return ['label' => $option];
                        }, explode(";", $parts[6]))) : null;
                }

                $elementForm = new ElementForm($elementData);

                if (!$elementForm->validate()) {
                    $message = "Incorrect element with key = {$variable}. Errors: " . implode(", ",
                            $elementForm->firstErrors);
                    throw new \Exception($message);
                }

                $elements[] = $elementData;

                $searchPatterns[] = $variable;
                $values[] = "\${{$key}}";
            }
        }

        $templateProcessor->setValue($searchPatterns, $values);

        $templateProcessor->saveAs($newPath);

        return new ElementsForm($id, ['elements' => $elements]);
    }


    /**
     * @param int $id
     * @param UploadedFile $file
     * @return DocumentForm
     */
    public function upload(int $id, UploadedFile $file)
    {
        /** @var DocumentForm $model */
        $model = DocumentForm::findOne($id);

        if (!$model) {
            throw new \InvalidArgumentException("Invalid id");
        }

        $oldDocPath = $model->doc_path;

        $model->doc_path = "/static/doc_templates/{$file->baseName}.docx";
        if ($model->update(false)) {
            if ($oldDocPath) {
                $path = \Yii::$app->basePath . '/..' . $oldDocPath;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $file->saveAs(\Yii::$app->basePath . '/..' . $model->doc_path);
        }

        return $model;
    }

    /**
     * @param TemplateProcessor $templateProcessor
     * @param string $key
     * @return null|array
     */
//    private function getOptions(TemplateProcessor $templateProcessor, string $key)
//    {
//        $block = $templateProcessor->cloneBlock($key . '_options', 1, false);
    /*        if ($block && preg_match_all('/<w:t.*?>(\([а-яА-ЯЁё]+)<\/w:t>/', $block, $matches)) {*/
//            $templateProcessor->deleteBlock($key . '_options');
//            return array_map(function ($match) {
//                return ['label' => $match];
//            }, $matches[1]);
//        }
//
//        return null;
//    }
}

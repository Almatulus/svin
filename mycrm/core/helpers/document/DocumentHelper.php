<?php

namespace core\helpers\document;

use core\models\document\Document;
use core\models\document\DocumentFormElement;
use core\models\document\DocumentValue;

class DocumentHelper
{
    public $searchPatterns = [];
    public $values = [];
    private $tooth_numbers = [
        11,12,13,14,15,16,17,18,
        21,22,23,24,25,26,27,28,
        31,32,33,34,35,36,37,38,
        41,42,43,44,45,46,47,48,
    ];

    /**
     * @param Document $document
     */
    public function generateSearchPatterns(Document $document)
    {
        foreach ($document->dentalCard as $element) {
            $this->searchPatterns[] = "tooth_" . $element->number;
            $this->values[] = $element->diagnosis->abbreviation;

            if (($key = array_search($element->number, $this->tooth_numbers)) !== false) {
                unset($this->tooth_numbers[$key]);
            }
        }

        if ($document->documentForm->has_services) {
            if ($document->services) {
                foreach ($document->services as $index => $service) {
                    $key = $index + 1;

                    $this->searchPatterns[] = "serviceNumber#{$key}";
                    $this->searchPatterns[] = "serviceName#{$key}";
                    $this->searchPatterns[] = "servicePrice#{$key}";
                    $this->searchPatterns[] = "serviceQuantity#{$key}";
                    $this->searchPatterns[] = "serviceSum#{$key}";

                    $this->values[] = $key;
                    $this->values[] = $service->service->service_name;
                    $this->values[] = intval($service->price / $service->quantity);
                    $this->values[] = $service->quantity;
                    $this->values[] = $service->price;
                }
            } else {
                $this->searchPatterns[] = "serviceNumber#1";
                $this->searchPatterns[] = "serviceName#1";
                $this->searchPatterns[] = "servicePrice#1";
                $this->searchPatterns[] = "serviceQuantity#1";
                $this->searchPatterns[] = "serviceSum#1";

                $this->values[] = "";
                $this->values[] = "";
                $this->values[] = "";
                $this->values[] = "";
                $this->values[] = "";
            }
        }

        foreach ($this->tooth_numbers as $toothNumber) {
            $this->searchPatterns[] = "tooth_" . $toothNumber;
            $this->values[] = "";
        }

        foreach ($document->values as $documentValue) {
            $value = $this->getValue($documentValue);
            if ($value !== null) {
                $this->searchPatterns[] = trim($documentValue->documentFormElement->key);
                $this->values[] = $value;
            }
        }

        foreach ($document->documentForm->elements as $element) {
            $key = array_search(trim($element->key), $this->searchPatterns);
            if (!$key) {
                $this->searchPatterns[] = trim($element->key);
                $this->values[] = "";
            }
        }
    }

    /**
     * @param DocumentValue $documentValue
     * @return null|string
     */
    public function getValue(DocumentValue $documentValue)
    {
        switch ($documentValue->documentFormElement->type) {
            case DocumentFormElement::TYPE_TEXT_INPUT:
            case DocumentFormElement::TYPE_TEXT:
                return $documentValue->value;
                break;
            case DocumentFormElement::TYPE_SELECT:
            case DocumentFormElement::TYPE_RADIOLIST:
                $options = $documentValue->documentFormElement->getDecodedOptions();
                return $options[$documentValue->value] ?? $documentValue->value;
                break;
            case DocumentFormElement::TYPE_CHECKBOX:
                if (boolval($documentValue->value)) {
                    return $documentValue->documentFormElement->label . ",";
                }
                break;
            case DocumentFormElement::TYPE_CHECKBOX_LIST:
                if ($documentValue->value) {
                    $selectedOptions = json_decode($documentValue->value);

                    $options = $documentValue->documentFormElement->getDecodedOptions();

                    return implode(", ", array_map(function ($optionKey) use ($options) {
                        return $options[$optionKey];
                    }, $selectedOptions));
                }
        }

        return null;
    }

}

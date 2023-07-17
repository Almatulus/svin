<?php

namespace core\forms\document;

use core\forms\medCard\MedCardTabServiceForm;
use core\models\customer\CompanyCustomer;
use core\models\customer\query\CompanyCustomerQuery;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use core\models\query\StaffQuery;
use core\models\Staff;
use core\repositories\exceptions\NotFoundException;
use yii\base\DynamicModel;

/**
 * Class CreateDocumentForm
 * @package core\forms\document
 * @property integer $customer_id
 * @property integer $manager_id
 * @property integer $staff_id
 */
class DocumentCreateForm extends DynamicModel
{
    /** @var DocumentForm */
    protected $_documentForm;

    /**
     * CreateDocumentForm constructor.
     * @param int $id
     * @param array $attributes
     * @param array $config
     */
    public function __construct(int $id, array $attributes = [], array $config = [])
    {
        $this->_documentForm = DocumentForm::findOne($id);

        if (!$this->_documentForm) {
            throw new NotFoundException('No document form found');
        }

        parent::__construct($attributes, $config);
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        foreach ($this->attributes() as $attribute) {
            $this->defineAttribute($attribute);
        }
    }

    /**
     * @return array
     */
    public function attributes()
    {
        $attributes = array_map(function (DocumentFormElement $element) {
            return $element->key;
        }, $this->_documentForm->getInputElements());

        if ($this->_documentForm->has_dental_card) {
            $attributes[] = 'dentalCard';
        }

        if ($this->_documentForm->has_services) {
            $attributes[] = 'services';
        }

        return array_merge($attributes, $this->customAttributes());
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = array_reduce($this->_documentForm->getInputElements(),
            function (array $result, DocumentFormElement $element) {
                return array_merge($result, $element->generateRules());
            }, []);

        if ($this->_documentForm->has_dental_card) {
            $rules = array_merge($rules, [
                ['dentalCard', 'validateDentalCard']
            ]);
        }

        if ($this->_documentForm->has_services) {
            $rules = array_merge($rules, [
                ['services', 'validateServices']
            ]);
        }

        return array_merge($this->customRules(), $rules);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = array_reduce($this->_documentForm->getInputElements(),
            function (array $result, DocumentFormElement $element) {
                $result[$element->key] = $element->label;
                return $result;
            }, []);

        if ($this->_documentForm->has_dental_card) {
            $attributeLabels['dentalCard'] = \Yii::t('app', 'Dental Card');
        }

        if ($this->_documentForm->has_dental_card) {
            $attributeLabels['services'] = \Yii::t('app', 'Services');
        }

        return array_merge($this->customAttributeLabels(), $attributeLabels);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_documentForm->id;
    }

    /**
     * @return bool
     */
    public function getHasDentalCard(): bool
    {
        return $this->_documentForm->has_dental_card;
    }

    /**
     * @return bool
     */
    public function getHasServices(): bool
    {
        return $this->_documentForm->has_services;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        $result = [];
        foreach ($this->_documentForm->getInputElements() as $element) {
            $result[$element->id] = $this->{$element->key};
        }
        return $result;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDentalCard($attribute, $params)
    {
        foreach ($this->{$attribute} as $tooth) {
            if (empty($tooth['diagnosis_id']) && empty($tooth['number'])) {
                continue;
            }
            $form = new ToothForm([
                'number'       => $tooth['number'] ?? null,
                'diagnosis_id' => $tooth['diagnosis_id'] ?? null,
                'mobility'     => $tooth['mobility'] ?? null
            ]);

            if (!$form->validate()) {
                $this->addError($attribute, current($form->firstErrors));
            }
        }
    }


    /**
     * @param $attribute
     * @param $params
     */
    public function validateServices($attribute, $params)
    {
        foreach ($this->{$attribute} as $service) {
            $form = new MedCardTabServiceForm([
                'division_service_id' => $service['service_id'] ?? null,
                'price'               => $service['price'] ?? null,
                'quantity'            => $service['quantity'] ?? null,
                'discount'            => $service['discount'] ?? null
            ]);

            if (!$form->validate()) {
                $this->addError($attribute, current($form->firstErrors));
            }
        }
    }

    /**
     * @return array
     */
    protected function customAttributes(): array
    {
        return [
            'customer_id',
            'manager_id',
            'staff_id'
        ];
    }

    /**
     * @return array
     */
    protected function customAttributeLabels(): array
    {
        return [
            'customer_id' => \Yii::t('app', 'Company Customer ID'),
            'manager_id'  => \Yii::t('app', 'Manager ID'),
            'staff_id'    => \Yii::t('app', 'Staff ID'),
        ];
    }

    /**
     * @return array
     */
    protected function customRules(): array
    {
        return [
            [['customer_id', 'staff_id'], 'required'],
            [['customer_id', 'manager_id', 'staff_id'], 'integer'],
            [
                'customer_id',
                'exist',
                'targetClass'     => CompanyCustomer::class,
                'targetAttribute' => 'id',
                'filter'          => function (CompanyCustomerQuery $query) {
                    $query->company();
                }
            ],
            [
                'manager_id',
                'exist',
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id',
                'filter'          => function (StaffQuery $query) {
                    $query->company()->permitted();
                }
            ],
            [
                'staff_id',
                'exist',
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id',
                'filter'          => function (StaffQuery $query) {
                    $query->company()->permitted();
                }
            ]
        ];
    }

}
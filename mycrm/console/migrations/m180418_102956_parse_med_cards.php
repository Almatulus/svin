<?php

use core\models\company\CompanyPosition;
use core\models\document\DocumentFormElement;
use core\models\medCard\MedCardCommentCategory;
use core\models\ServiceCategory;
use yii\db\Migration;

/**
 * Class m180418_102956_parse_med_cards
 */
class m180418_102956_parse_med_cards extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%document_values}}', 'value', $this->text());

        $newForm = new \core\models\document\DocumentForm([
            'name'         => 'Стандартная форма',
            'has_services' => true,
            'has_dental_card' => true,
        ]);

        $elements = [];
        if ($newForm->save()) {

            $elements[0] = new DocumentFormElement([
                'document_form_id' => $newForm->id,
                'label'            => 'Диагноз',
                'key'              => 'diagnosis_id',
                'order'            => 1,
                'raw_id'           => 1,
                'type'             => DocumentFormElement::TYPE_SELECT,
                'search_url'       => "diagnosis?service_category_id=" . ServiceCategory::ROOT_STOMATOLOGY
            ]);
            $elements[0]->save(false);


            $comment_categories = MedCardCommentCategory::find()
                ->serviceCategory(ServiceCategory::ROOT_STOMATOLOGY)
                ->orderBy('order')
                ->all();

            foreach ($comment_categories as $key => $comment_category) {
                $elements[$comment_category->id] = new DocumentFormElement([
                    'document_form_id' => $newForm->id,
                    'label'            => $comment_category->name,
                    'key'              => $comment_category->id + 1,
                    'order'            => $comment_category->id,
                    'raw_id'           => 1,
                    'type'             => DocumentFormElement::TYPE_TEXT_INPUT,
                    'search_url'       => "comment/default/index?category_id=" . $comment_category->id,
                    'depends_on'       => 'diagnosis_id'
                ]);
                $elements[$comment_category->id]->save(false);
            }
        }

        $medCards = \core\models\medCard\MedCard::find();

        /** @var \core\models\medCard\MedCard $medCard */
        foreach ($medCards->each(100) as $medCard) {
            foreach ($medCard->tabs as $tab) {
                $document = new \core\models\document\Document([
                    'document_form_id'    => $newForm->id,
                    'company_customer_id' => $medCard->order->company_customer_id,
                    'staff_id'            => $medCard->order->staff_id,
                    'created_at'          => $medCard->order->datetime,
                    'updated_at'          => $medCard->order->datetime
                ]);
                $document->detachBehavior('timestampBehavior');

                if ($document->save(false)) {

                    foreach ($tab->services as $service) {
                        $docService = new \core\models\document\DocumentService([
                            'document_id' => $document->id,
                            'service_id'  => $service->division_service_id,
                            'quantity'    => $service->quantity,
                            'price'       => $service->price,
                            'discount'    => $service->discount
                        ]);
                        $docService->save(false);
                    }

                    if ($tab->diagnosis_id) {
                        $docValue = new \core\models\document\DocumentValue([
                            'document_id'              => $document->id,
                            'document_form_element_id' => $elements[0]->id,
                            'value'                    => $tab->diagnosis->id
                        ]);
                        $docValue->save(false);
                    }

                    foreach ($tab->teeth as $tooth) {
                        $docTooth = new core\models\document\DentalCardElement([
                            'document_id'  => $document->id,
                            'number'       => $tooth->teeth_num,
                            'diagnosis_id' => $tooth->teeth_diagnosis_id,
                            'mobility'     => $tooth->mobility,
                        ]);
                        $docTooth->save(false);
                    }

                    foreach ($tab->comments as $comment) {
                        $docValue = new \core\models\document\DocumentValue([
                            'document_id'              => $document->id,
                            'document_form_element_id' => $elements[$comment->category_id]->id,
                            'value'                    => $comment->comment
                        ]);
                        $docValue->save(false);
                    }
                }
            }
        }

        foreach (CompanyPosition::find()->company(176)->each() as $position) {
            $position->link('documentForms', $newForm);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $newForms = \core\models\document\DocumentForm::find()->where([
            'name'         => 'Стандартная форма',
            'has_services' => true
        ])->select('id')->column();

        if ($newForms) {

            foreach ($newForms as $form_id) {
                $form = \core\models\document\DocumentForm::findOne($form_id);
                if (!$form) {
                    continue;
                }
                foreach (
                    CompanyPosition::find()->company(176)->each() as $position
                ) {
                    /* @var CompanyPosition $position */
                    $position->unlink('documentForms', $form, true);
                }
            }

            $docIds = \core\models\document\Document::find()->where([
                'document_form_id' => $newForms
            ])->select('id')->column();

            if ($docIds) {
                \core\models\document\DentalCardElement::deleteAll([
                    'document_id' => $docIds
                ]);
                \core\models\document\DocumentService::deleteAll([
                    'document_id' => $docIds
                ]);
                \core\models\document\DocumentValue::deleteAll([
                    'document_id' => $docIds
                ]);
            }
            \core\models\document\Document::deleteAll([
                'document_form_id' => $newForms
            ]);
            \core\models\document\DocumentForm::deleteAll([
                'id' => $newForms
            ]);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180418_102956_parse_med_cards cannot be reverted.\n";

        return false;
    }
    */
}

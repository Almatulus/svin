<?php
/* @var $insuranceCompanies \core\models\InsuranceCompany[] */
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;

DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_insurance_companies', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-insurance-companies', // required: css class selector
    'widgetItem' => '.dynamic-insurance-company', // required: css class
    'limit' => 50, // the maximum times, an element can be cloned (default 999)
    'min' => 0, // 0 or 1 (default 1)
    'insertButton' => '.add-insurance-company', // css class
    'deleteButton' => '.remove-insurance-company', // css class
    'model' => $insuranceCompanies[0],
    'formId' => $form->getId(),
    'formFields' => [
        'insurance_company_id',
        'price',
        'price_max',
    ],
]); ?>

    <div class="data_table no_hover">
        <table>
            <thead>
            <tr>
                <th><?= Yii::t('app', 'Insurance Company') ?></th>
                <th><?= Yii::t('app', 'Price') ?></th>
                <th><?= Yii::t('app', 'Delete') ?></th>
            </tr>
            </thead>
            <tbody class="container-insurance-companies">
            <?php
            foreach ($insuranceCompanies as $index => $insuranceCompany):
                echo $this->render("_insurance_company", [
                    'form' => $form,
                    'model' => $insuranceCompany,
                    'index' => $index
                ]);
            endforeach;
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td><?= Html::button(Yii::t('app', 'Add'), ['class' => 'btn add-insurance-company'])?></td>
                <td class="summary right_text" colspan="5">
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

<?php DynamicFormWidget::end(); ?>
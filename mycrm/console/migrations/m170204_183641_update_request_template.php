<?php

use core\helpers\customer\RequestTemplateHelper;
use core\models\customer\CustomerRequestTemplate;
use yii\db\Migration;

class m170204_183641_update_request_template extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $templates = CustomerRequestTemplate::find()->filterWhere(['like', 'template', '%TITLE%', false])->all();
        foreach ($templates as $template) {
            /* @var CustomerRequestTemplate $template */
            $template->template = str_replace('TITLE', RequestTemplateHelper::COMPANY_NAME, $template->template);
            $template->save();
        }
    }

    public function safeDown()
    {
        $templates = CustomerRequestTemplate::find()
            ->filterWhere(['like', 'template', '%' . RequestTemplateHelper::COMPANY_NAME . '%', false])->all();
        foreach ($templates as $template) {
            /* @var CustomerRequestTemplate $template */
            $template->template = str_replace(RequestTemplateHelper::COMPANY_NAME, 'TITLE', $template->template);
            $template->save();
        }
    }
}

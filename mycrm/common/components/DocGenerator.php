<?php

namespace common\components;

use core\models\order\OrderDocumentTemplate;
use PhpOffice\PhpWord\TemplateProcessor;
use Yii;

class DocGenerator
{
    /**
     * @param $template_id
     * @param $searchPatterns
     * @param $values
     * @param $order_id
     * @param $date
     *
     * @return string
     */
    public static function generateTemplate(
        $template_id,
        $searchPatterns,
        $values,
        $order_id,
        $date
    ) {
        $template      = OrderDocumentTemplate::findOne($template_id);
        $template_path = Yii::getAlias('@static')."/doc_templates/{$template->filename}";

        $templateProcessor = new TemplateProcessor($template_path);
        $templateProcessor->setValue($searchPatterns, $values);

        $filename = $template->name."_".str_replace([" ", "-", ":"], "_", $date).".docx";

        $root_path = Yii::getAlias('@runtime');
        $path      = self::getPath($root_path, $filename, $order_id);

        $templateProcessor->saveAs($path);

        return $path;
    }

    /**
     * Returns relative path
     *
     * @param string  $root_path
     * @param string  $filename
     * @param integer $order_id
     *
     * @return string
     */
    private static function getPath($root_path, $filename, $order_id)
    {
        $path      = "/docs/";
        $real_path = $root_path.$path;
        if ( ! file_exists($real_path) && ! is_dir($real_path)) {
            mkdir($real_path);
        }

        $path      = $path.Yii::$app->user->identity->company_id."/";
        $real_path = $root_path."/".$path;
        if ( ! file_exists($real_path) && ! is_dir($real_path)) {
            mkdir($real_path);
        }

        $path      = $path.$order_id."/";
        $real_path = $root_path."/".$path;
        if ( ! file_exists($real_path) && ! is_dir($real_path)) {
            mkdir($real_path);
        }

        return $root_path.$path.$filename;
    }
}

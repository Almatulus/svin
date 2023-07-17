<?php

namespace common\components\parsers;

use core\models\company\Company;
use core\forms\ImportForm;
use PHPExcel_Worksheet;
use yii\base\Component;

abstract class BaseParser extends Component
{
    protected $_savedCounter = 0;
    protected $_incorrectModels = [];

    /**
     * @param ImportForm $model
     * @param Company $company
     */
    public function execute(ImportForm $model, Company $company)
    {
        $loadedFile = \PHPExcel_IOFactory::load($model->excelFile->tempName);
        $sheet = $loadedFile->getSheet(0);

        $this->_savedCounter = 0;
        $this->_incorrectModels = [];

        \Yii::$app->session->set('progress', 1);

        $this->parse($sheet, $company);
    }

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param Company $company
     */
    public abstract function parse(PHPExcel_Worksheet $sheet, Company $company);

    /**
     * @return int
     */
    public function getSavedCounter(): int
    {
        return $this->_savedCounter;
    }

    /**
     * @return array
     */
    public function getIncorrectModels(): array
    {
        return $this->_incorrectModels;
    }

    /**
     * @param $option
     * @return bool
     */
    protected static function getOption($option)
    {
        if (strtolower($option) == 'да') {
            return true;
        }
        return false;
    }
}
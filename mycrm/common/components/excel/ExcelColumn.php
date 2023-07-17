<?php

namespace common\components\excel;

class ExcelColumn
{
    public $format;
    public $value;

    /**
     * ExcelColumn constructor.
     * @param string $value
     * @param string|null $format
     */
    public function __construct(string $value = null, string $format = null)
    {
        $this->format = $format;
        $this->value = $value;
    }

    /**
     * @return null|string
     */
    public function getFormat()
    {
        switch ($this->format) {
            case 'number':
                return \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
            case 'date':
                return \PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME;
        }
        return \PHPExcel_Style_NumberFormat::FORMAT_TEXT;
    }

    /**
     * @return false|int|string
     */
    public function getValue()
    {
        if ($this->format == 'date' && $this->value !== null) {
            return \PHPExcel_Shared_Date::PHPToExcel(strtotime($this->value));
        }
        return $this->value;
    }
}
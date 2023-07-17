<?php

namespace common\components\excel;

use PHPExcel_Cell;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Excel
 * @package common\components\excel
 */
class Excel extends Component
{
    /**
     * @var string
     */
    public $creator;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $showFooter = false;

    /**
     * @var \yii\db\ActiveRecord[]
     */
    public $models;

    /**
     * @var array
     *
     * ```php
     * [
     *     [
     *         'attribute' => 'name',
     *         'format' => 'text',
     *         'label' => 'Name',
     *         'value' => 'fullName'
     *     ],
     * ]
     * ```
     */
    public $columns;

    /**
     * @param ExcelFileConfig $config
     * @param ExcelRow[]      $rows
     *
     * @throws \PHPExcel_Writer_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Exception
     */
    public function generateReport(ExcelFileConfig $config, array $rows)
    {
        ob_start();

        $excelObject = new \PHPExcel();
        $excelObject->getProperties()
                    ->setCreator($config->creator)
                    ->setTitle($config->title)
                    ->setLastModifiedBy($config->creator)
                    ->setDescription($config->description)
                    ->setSubject($config->description)
                    ->setKeywords($config->keywords)
                    ->setCategory($config->category);

        $excelSheet = $excelObject->getSheet(0);
        $excelSheet->setTitle($config->title);

        $row_id = 1;
        foreach ($rows as $row) {
            $column_id = 'A';
            foreach ($row->getData() as $value) {
                $excelObject->getActiveSheet()
                            ->getStyle($column_id . $row_id)
                            ->getFont()
                            ->setBold($row->isBold());
                $excelSheet->getColumnDimension($column_id)->setAutoSize(true);

                if ($value instanceof ExcelColumn) {
                    $excelSheet->setCellValue($column_id . $row_id, $value->getValue());
                    if ($value->format) {
                        $excelSheet->getStyle($column_id . $row_id)->getNumberFormat()->setFormatCode($value->getFormat());
                    }
                } else {
                    $excelSheet->setCellValue($column_id . $row_id, $value);
                }

                $columnNumber = PHPExcel_Cell::columnIndexFromString($column_id) + 1;
                $column_id = PHPExcel_Cell::stringFromColumnIndex($columnNumber - 1);
            }
            $row_id++;
        }

        header('Content-Type: application/vnd.ms-excel');
        $filename = $config->filename . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        header('Access-Control-Allow-Origin: *');

        $objWriter = \PHPExcel_IOFactory::createWriter($excelObject, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }

    /**
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export()
    {
        if (empty($this->models)) {
            throw new \InvalidArgumentException("Please specify models");
        }

        $rows = $this->generateItems();

        $this->generateReport($this->generateFileConfig(), $rows);
    }

    /**
     * @return array
     */
    private function generateItems()
    {
        $header = $this->generateHeader();
        $body = $this->generateBody();

        $rows = array_merge([$header], $body);
        if ($this->showFooter) {
            $rows = array_merge($rows, [$this->generateFooter()]);
        }
        return $rows;
    }

    /**
     * @return ExcelRow
     */
    private function generateHeader()
    {
        $cells = [];
        foreach ($this->columns as $columnData) {
            if (!isset($columnData['label'])) {
                $model = reset($this->models);
                $cells[] = $model->getAttributeLabel($columnData['attribute']);
            } else {
                $cells[] = $columnData['label'];
            }
        }
        return new ExcelRow($cells, true);
    }

    /**
     * @return array
     */
    private function generateBody()
    {
        $rows = [];
        foreach ($this->models as $index => $model) {
            $data = [];
            foreach ($this->columns as $columnData) {
                $key = $columnData['value'] ?? $columnData['attribute'];
                $value = ArrayHelper::getValue($model, $key);
                $format = $columnData['format'] ?? null;
                $data[] = new ExcelColumn($value, $format);
            }
            $rows[] = new ExcelRow($data);
        }
        return $rows;
    }

    /**
     * @return ExcelRow
     */
    private function generateFooter()
    {
        $cells = [];
        foreach ($this->columns as $columnData) {
            $format = $columnData['format'] ?? null;
            $value = $columnData['footer'] ?? null;
            $cells[] = new ExcelColumn($value, $format);
        }
        return new ExcelRow($cells, true);
    }

    /**
     * @return ExcelFileConfig
     */
    private function generateFileConfig()
    {
        return new ExcelFileConfig($this->filename, $this->creator, $this->title);
    }
}
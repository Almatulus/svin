<?php

namespace core\forms\finance;

use core\helpers\order\OrderConstants;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\finance\query\CashflowQuery;
use core\models\order\query\OrderQuery;
use core\models\Payment;
use PHPExcel_Worksheet;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @TODO Refactor. Move code to ../forms folder
 */
class DailyReportForm extends Model
{
    const COLUMN_DATETIME = 0;
    const COLUMN_CREATED_AT = 1;
    const COLUMN_STAFF = 2;
    const COLUMN_CUSTOMER = 3;
    const COLUMN_COST_ITEM = 4;
    const COLUMN_SERVICE = 5;
    const COLUMN_PAID = 6;
    const COLUMN_DISCOUNT = 7;
    const COLUMN_CASH = 8;
    const COLUMN_DEPT = 9;
    const COLUMN_INSURANCE_COMPANY = 10;
    const COLUMN_EMPLOYER_COMPANY = 11;
    const COLUMN_MED_RECORD_ID = 12;
    const COLUMN_INSURANCE_POLICY = 13;

    public $cash;
    public $cost_item;
    public $division;
    public $end;
    public $staff;
    public $start;
    public $insurance_company;
    public $insurer;
    public $division_service;
    public $service_category;
    public $total_only;

    public $columns = [
        self::COLUMN_DATETIME,
        self::COLUMN_CREATED_AT,
        self::COLUMN_STAFF,
        self::COLUMN_CUSTOMER,
        self::COLUMN_COST_ITEM,
        self::COLUMN_SERVICE,
        self::COLUMN_PAID,
        self::COLUMN_DISCOUNT,
        self::COLUMN_CASH,
        self::COLUMN_DEPT,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->start = date("Y-m-d");
        $this->end = date("Y-m-d");
        $this->groupBy = self::COLUMN_STAFF;
        $this->total_only = false;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['cash', 'cost_item', 'division', 'staff', 'insurance_company', 'division_service', 'service_category'], 'integer'],
            ['insurer', 'string', 'max' => 255],
            [['end', 'start'], 'date', 'format' => "Y-m-d"],
            ['total_only', 'boolean'],
            [['groupBy', 'visibleColumns'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return array
     */
    public function search()
    {
        switch ($this->groupBy) {
            case self::COLUMN_STAFF:
                $column = 'staff_id';
                break;
            case self::COLUMN_CUSTOMER:
                $column = 'customer_id';
                break;
            case self::COLUMN_COST_ITEM:
                $column = 'cost_item_id';
                break;
            default:
                $column = 'staff_id';
//                throw new \DomainException('Invalid groupBy value');
                break;
        }

        $models = $this->getCashflows();

        return ArrayHelper::index($models, null, [
            $column,
            function (CompanyCashflow $cashflow) {
                return $cashflow->order->id;
            }]);
    }

    /**
     * @return mixed
     */
    public function getCashflows()
    {
        $endDate = new \DateTime($this->end);
        $endDate->modify("+1 day");
        return $this->getQuery()
            ->range($this->start, $endDate->format('Y-m-d'))
            ->joinWith([
                'products.product',
                'services.service',
                'payments',
                "costItem",
                "staff",
                "cash",
                "customer.customer",
            ])
            ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
            ->orderBy(['{{%company_cashflows}}.created_at' => SORT_ASC, 'staff_id' => SORT_DESC])
            ->all();
    }

    /**
     * @return array
     */
    public function getPreviousDayBalance()
    {
        $cashflows = $this->getQuery()->until($this->start);

        $balancePaid = (clone($cashflows))->income()->sum('value') - (clone($cashflows))->expense()->sum('value');

        $balanceProductsDiscount = 0;

        $subQuery = CompanyCashflow::find()
            ->select(['{{%company_cashflows}}.order_id', 'MAX({{%company_cashflows}}.created_at) as created_at'])
            ->company(\Yii::$app->user->identity->company_id)
            ->active()
            ->permittedDivisions()
            ->until($this->start)
            ->cash($this->cash)
            ->costItem($this->cost_item)
            ->division($this->division)
            ->staff($this->staff)
            ->innerJoinWith([
                'order' => function (OrderQuery $orderQuery) {
                    return $orderQuery->finished();
                }
            ], false)
            ->groupBy('{{%company_cashflows}}.order_id');

        $balanceServicesDiscount = CompanyCashflow::find()
            ->innerJoinWith(['order', 'services'], false)
            ->innerJoin(['cc' => $subQuery],
                'cc.order_id = {{%orders}}.id AND cc.created_at = {{%company_cashflows}}.created_at')
            ->income()
            ->andWhere(['<>', "{{%company_cashflow_services}}.discount", 100])
            ->sum('{{%company_cashflow_services}}.price * discount / 100');

        return [
            'paid'     => $balancePaid,
            'discount' => $balanceProductsDiscount + $balanceServicesDiscount,
        ];
    }

    /**
     * @return CashflowQuery
     */
    private function getQuery()
    {
        $query = CompanyCashflow::find()
            ->company(\Yii::$app->user->identity->company_id)
            ->active()
            ->permittedDivisions()
            ->cash($this->cash)
            ->costItem($this->cost_item)
            ->division($this->division)
            ->staff($this->staff)
            ->innerJoinWith(['order']);

        if ($this->division_service) {
            $query->joinWith(['order.orderServices']);
            $query->andWhere(['{{%order_services}}.division_service_id' => $this->division_service]);
        }

        if ($this->service_category) {
            $query->joinWith(['order.orderServices.divisionService.categories']);
            $query->andWhere(['{{%service_categories}}.id' => $this->service_category]);
        }

        return $query;
    }

    /**
     * Exports excel file
     * @param $paymentsList
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportExcel($paymentsList)
    {
        ob_start();

        $ea = new \PHPExcel(); // ea is short for Excel Application
        $ea->getProperties()
            ->setCreator('Reone')
            ->setTitle('PHPExcel')
            ->setLastModifiedBy('Reone')
            ->setDescription('')
            ->setSubject('')
            ->setKeywords('excel php')
            ->setCategory('');
        $ews = $ea->getActiveSheet();
        $sheet_title = iconv_substr(
            'Отчет_' . Yii::$app->formatter->asDate($this->start, "php:d F")
            . '-'
            . Yii::$app->formatter->asDate($this->start,
                "medium"
            ),
            0,
            30
        );
        $ews->setTitle($sheet_title);

        // Write cells
        $this->fillHeader($ews, $paymentsList);
        $this->fillBody($ews, $paymentsList);
        $this->setAutoSize($ews, $paymentsList);
        $this->hideColumns($ews);

        $filename = "Ежедневный_Отчет_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        header('Content-Type: application/vnd.ms-excel');
        $objWriter = \PHPExcel_IOFactory::createWriter($ea, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }

    /**
     * Fills sheet header cells
     */
    private function fillHeader($sheet, $paymentsList)
    {
        $headerColumnIndex = 0;
        $headerRowIndex = 1;
//        $headerCells = self::getHeader($this->group_by);
        foreach ($this->columns as $column) {
            $cellName = self::getColumnLabels()[$column];
            $this->writeCellValue($sheet, $headerColumnIndex++, $headerRowIndex, $cellName, true);
        }
        foreach ($paymentsList as $id => $paymentName) {
            $this->writeCellValue($sheet, $headerColumnIndex++, $headerRowIndex,  Yii::t('app', $paymentName), true);
        }
    }

    /**
     * Fills sheet body cells
     *
     * @param PHPExcel_Worksheet $sheet
     * @param                    $paymentsList
     */
    private function fillBody(PHPExcel_Worksheet $sheet, $paymentsList)
    {
        $row = 2;
        $endBalanceRows = [$row];

        $models = $this->search();

        $this->writeStartBalance($sheet, $row);
        $row++;

        $intermediateRow = $row;

        foreach ($models as $staff_id => $staffCashflows) {
            $staff = null;
            foreach ($staffCashflows as $order_id => $cashflows) {
                $rowspan = sizeof($cashflows) - 1;
                foreach ($cashflows as $key => $cashflow) {
                    /* @var CompanyCashflow $cashflow */
                    $columnIndex = 0;
                    $staff = $cashflow->staff;

                    foreach ($this->columns as $column) {
                        switch ($column) {
                            case self::COLUMN_STAFF:
                            case self::COLUMN_CREATED_AT:
                                $this->writeCellValue($sheet, $columnIndex++, $row, $cashflow->staff->getFullName());
                                break;

                            case self::COLUMN_DATETIME:
                                if ($key == 0) {
                                    $timestampDate = strtotime($cashflow->order->datetime . " +6 hours");
                                    $this->writeCellValue($sheet, $columnIndex++, $row, \PHPExcel_Shared_Date::PHPToExcel($timestampDate));
                                    $sheet->mergeCellsByColumnAndRow($columnIndex - 1, $row, $columnIndex - 1, $row + $rowspan);
                                    $sheet->getStyleByColumnAndRow($columnIndex - 1, $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
                                    $sheet->mergeCellsByColumnAndRow($columnIndex - 1, $row, $columnIndex - 1, $row + $rowspan);
                                } else {
                                    $columnIndex++;
                                }
                                break;

                            case self::COLUMN_CUSTOMER:
                                $this->writeCellValue($sheet, $columnIndex++, $row, $cashflow->customer->customer->getFullName());
                                break;

                            case self::COLUMN_INSURANCE_COMPANY:
                                $order = $cashflow->getOrder();
                                $has_insurance = !empty($order->insurance_company_id);

                                if ($has_insurance) {
                                    $value = $order->insuranceCompany->name;
                                } else {
                                    $value = Yii::t('yii', '');
                                }

                                $this->writeCellValue($sheet, $columnIndex++, $row, $value);
                                break;

                            case self::COLUMN_EMPLOYER_COMPANY:
                                $order = $cashflow->getOrder();
                                $has_insurance = !empty($order->insurance_company_id);

                                if ($has_insurance) {
                                    $value = $order->companyCustomer->insurer;
                                } else {
                                    $value = Yii::t('yii', '');
                                }

                                $this->writeCellValue($sheet, $columnIndex++, $row, $value);
                                break;

                            case self::COLUMN_INSURANCE_POLICY:
                                $order = $cashflow->order;
                                $has_insurance = !empty($order->insurance_company_id);

                                if ($has_insurance) {
                                    $value = $order->companyCustomer->insurance_policy_number;
                                } else {
                                    $value = Yii::t('yii', '');
                                }

                                $this->writeCellValue($sheet, $columnIndex++, $row, $value);
                                break;

                            case self::COLUMN_MED_RECORD_ID:
                                $this->writeCellValue($sheet, $columnIndex++, $row, $cashflow->customer->medical_record_id);
                                break;

                            case self::COLUMN_COST_ITEM:
                                $this->writeCellValue($sheet, $columnIndex++, $row, Yii::t('app', $cashflow->costItem->name));
                                break;

                            case self::COLUMN_SERVICE:
                                $name = $cashflow->getItemsTitle(', ');

                                $this->writeCellValue($sheet, $columnIndex++, $row, $name);
                                break;

                            case self::COLUMN_PAID:
                                $value = $cashflow->value;
                                $value *= $cashflow->costItem->type === CompanyCostItem::TYPE_EXPENSE ? -1 : 1;
                                $this->writeCellValue($sheet, $columnIndex++, $row, $value);
                                $sheet->getStyleByColumnAndRow($columnIndex - 1, $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                break;

                            case self::COLUMN_DISCOUNT:
                                $this->writeCellValue($sheet, $columnIndex++, $row, $cashflow->getDiscount());
                                $sheet->getStyleByColumnAndRow($columnIndex - 1, $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                break;

                            case self::COLUMN_CASH:
                                if ($key == 0) {
                                    $this->writeCellValue($sheet, $columnIndex++, $row, $cashflow->cash->name);
                                }
                                break;

                            case self::COLUMN_DEPT:
                                if ($key == 0) {
                                    $this->writeCellValue($sheet, $columnIndex++, $row,
                                        $cashflow->order->payment_difference < 0 ? abs($cashflow->order->payment_difference) : 0);
                                    $sheet->getStyleByColumnAndRow($columnIndex - 1,
                                        $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                    $sheet->mergeCellsByColumnAndRow($columnIndex - 1, $row, $columnIndex - 1, $row + $rowspan);
                                }
                                break;

                            default:
                                throw new \DomainException('Invalid groupBy value: ' . $column);
                                break;
                        }
                    }

                    if ($key == 0) {
                        $orderPayments = ArrayHelper::map($cashflow->order->orderPayments, 'payment_id', 'amount');
                        foreach ($paymentsList as $id => $paymentName) {
                            $value = isset($orderPayments[$id]) ? $orderPayments[$id] : 0;
                            $this->writeCellValue($sheet, $columnIndex++, $row, $value);
                            $sheet->mergeCellsByColumnAndRow($columnIndex - 1, $row, $columnIndex - 1, $row + $rowspan);
                        }
                    }
                    $row++;
                }
            }

            $title = $this->total_only ? $staff->getFullName() : "Итого";
            $this->setFormulas($sheet, $title, $intermediateRow, $row);
            $endBalanceRows[] = $row;
            $row++;
            $intermediateRow = $row;
        }

        $this->writeEndBalance($sheet, "Остаток на конец дня", $row, $endBalanceRows);
    }

    private function writeCellValue(PHPExcel_Worksheet $sheet, $column, $row, $value, $is_total = false) {
        $sheet->setCellValueByColumnAndRow($column, $row, $value);
        if (!$is_total && $this->total_only) {
            $sheet->getRowDimension($row)->setVisible(false);
        }
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     * @param $row
     */
    private function writeStartBalance($sheet, $row)
    {
        $yesterdayBalance = $this->getPreviousDayBalance();
        $this->writeCellValue($sheet, 0, $row, "Остаток на начало дня", true);
        $sheet->mergeCells("A{$row}:D{$row}");
        $this->writeCellValue($sheet, 10, $row, $yesterdayBalance['paid'], true);
        $sheet->getStyleByColumnAndRow(10, $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $this->writeCellValue($sheet, 11, $row, $yesterdayBalance['discount'], true);
        $sheet->getStyleByColumnAndRow(11, $row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     * @param $title
     * @param $row
     * @param $rows
     */
    private function writeEndBalance($sheet, $title, $row, $rows)
    {
        $this->writeCellValue($sheet, 0, $row, $title, true);
        $sheet->mergeCells("A{$row}:D{$row}");
        $paidFormula = $discountFormula = $debtFormula = "";
        foreach ($rows as $key => $value) {
            $paidFormula .= ($key == 0) ? "K{$value}" : "+K{$value}";
            $discountFormula .= ($key == 0) ? "L{$value}" : "+L{$value}";
        }
        $this->writeCellValue($sheet, 10, $row, "={$paidFormula}", true);
        $this->writeCellValue($sheet, 11, $row, "={$discountFormula}", true);
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     * @param $title
     * @param $start_row
     * @param $end_row
     */
    private function setFormulas($sheet, $title, $start_row, $end_row)
    {
        $this->writeCellValue($sheet, 0, $end_row, $title, true);
        $sheet->mergeCells("A{$end_row}:D{$end_row}");
        $rowIndex = $end_row - 1;
        $columnChar = \PHPExcel_Cell::stringFromColumnIndex(10);
        $this->writeCellValue($sheet, 10, $end_row, "=SUM({$columnChar}{$start_row}:{$columnChar}" . $rowIndex . ")", true);
        $columnChar = \PHPExcel_Cell::stringFromColumnIndex(11);
        $this->writeCellValue($sheet, 11, $end_row, "=SUM({$columnChar}{$start_row}:{$columnChar}" . $rowIndex . ")", true);
        $columnChar = \PHPExcel_Cell::stringFromColumnIndex(13);
        $this->writeCellValue($sheet, 13, $end_row, "=SUM({$columnChar}{$start_row}:{$columnChar}" . $rowIndex . ")", true);
        $columnChar = \PHPExcel_Cell::stringFromColumnIndex(14);
        $this->writeCellValue($sheet, 14, $end_row, "=SUM({$columnChar}{$start_row}:{$columnChar}" . $rowIndex . ")", true);
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     * @param $paymentsList
     */
    private function setAutoSize($sheet, $paymentsList)
    {
        $nCols = sizeof($this->columns) + sizeof($paymentsList);
        foreach (range(0, $nCols) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     */
    private function hideColumns($sheet)
    {
        $count = 0;
        foreach ($this->columns as $key => $column) {
            if(!$this->visibleColumns[$column]) {
                $sheet->getColumnDimensionByColumn($count)->setVisible(false);
            }
            $count++;
        }
    }

    public function isGroupedByStaff()
    {
        return $this->_groupBy == self::COLUMN_STAFF;
    }

    public function isGroupedByCustomer()
    {
        return $this->_groupBy == self::COLUMN_CUSTOMER;
    }

    public function isGroupedByCostItem()
    {
        return $this->_groupBy == self::COLUMN_COST_ITEM;
    }

    public static function getGroupByList()
    {
        return [
            self::COLUMN_STAFF => Yii::t('app', 'Staff ID'),
            self::COLUMN_CUSTOMER => Yii::t('app', 'Customer'),
            self::COLUMN_COST_ITEM => Yii::t('app', 'Name'),
            self::COLUMN_SERVICE => "Услуга/Товар",
            self::COLUMN_DATETIME => Yii::t('app', 'Datetime'),
        ];
    }

    public static function getColumnLabels()
    {
        return [
            self::COLUMN_DATETIME          => Yii::t('app', 'Datetime'),
            self::COLUMN_CREATED_AT        => Yii::t('app', 'Created at'),
            self::COLUMN_STAFF             => Yii::t('app', 'Staff ID'),
            self::COLUMN_CUSTOMER          => Yii::t('app', 'Customer'),
            self::COLUMN_INSURANCE_COMPANY => Yii::t('app', 'Insurance Company'),
            self::COLUMN_INSURANCE_POLICY  => Yii::t('app', 'Insurance policy number'),
            self::COLUMN_EMPLOYER_COMPANY  => Yii::t('app', 'Insurer'),
            self::COLUMN_MED_RECORD_ID     => Yii::t('app', 'Number of medical record'),
            self::COLUMN_COST_ITEM         => Yii::t('app', 'Name'),
            self::COLUMN_SERVICE           => "Услуга/Товар",
            self::COLUMN_PAID              => Yii::t('app', 'Paid, currency'),
            self::COLUMN_DISCOUNT          => Yii::t('app', 'Discount, currency'),
            self::COLUMN_CASH              => Yii::t('app', 'Cash'),
            self::COLUMN_DEPT              => Yii::t('app', 'Debt'),
        ];
    }

    private $_groupBy;

    public function getGroupBy()
    {
        return $this->_groupBy;
    }

    public function setGroupBy($value)
    {
        $this->_groupBy = $value;
        $this->columns = array($value => $this->columns[$value]) + $this->columns;
    }

    private $_visibleColumns = null;

    public function getVisibleColumns()
    {
        if ($this->_visibleColumns === null) {
            $cookie = Yii::$app->request->cookies->getValue($this->formName() . 'visible-columns', "[]");
            if($cookie !== null) {
                $this->_visibleColumns = Json::decode($cookie);
            } else {
                $this->_visibleColumns = [];
            }

            foreach ($this->columns as $column) {
                if(!array_key_exists($column, $this->_visibleColumns)) {
                    $this->_visibleColumns[$column] = 1;
                }
            }
        }

        return $this->_visibleColumns;
    }

    public function setVisibleColumns($value)
    {
        $this->_visibleColumns = $value;
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => $this->formName() . 'visible-columns',
            'value' => Json::encode($this->_visibleColumns),
        ]));
    }

    /**
     * Gets list of payment methods. Only methods which was paid with.
     * @return array
     */
    public function getPaymentList(): array
    {
        $paymentsListQuery = Payment::find()
            ->select(['{{%payments}}.id', '{{%payments}}.name'])
            ->innerJoinWith(['cashflowPayments.cashflow.division'], false)
            ->andWhere(['>=', '{{%company_cashflows}}.date', $this->start . " 00:00:00"])
            ->andWhere(['<=', '{{%company_cashflows}}.date', $this->end . " 24:00:00"])
            ->andWhere(['{{%divisions}}.company_id' => Yii::$app->user->identity->company_id])
            ->andFilterWhere(['{{%company_cashflows}}.division_id' => $this->division])
            ->andFilterWhere(['{{%company_cashflows}}.staff_id' => $this->staff])
            ->groupBy('{{%payments}}.id')
            ->having('SUM({{%company_cashflow_payments}}.value) > 0')
            ->asArray();

        if ($this->cost_item && ($costItem = CompanyCostItem::findOne($this->cost_item))) {
            if ($costItem->cost_item_type == CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE) {
                $paymentsListQuery->innerJoinWith(['cashflowPayments.cashflow.products'], false);
            } else {
                if ($costItem->cost_item_type == CompanyCostItem::COST_ITEM_TYPE_SERVICE) {
                    $paymentsListQuery->innerJoinWith(['cashflowPayments.cashflow.services'], false);
                }
            }
        }

        return ArrayHelper::map($paymentsListQuery->all(), "id", "name");
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
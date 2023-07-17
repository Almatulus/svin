<?php

namespace core\forms\finance;

use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use Yii;
use yii\base\Model;

/**
 * @TODO Refactor. Move code to ../forms folder
 * ReportForm is for Financial Report
 * @package core\forms\finance
 *
 * @property integer $from
 * @property integer $to
 * @property integer $cash
 * @property boolean $showDetailing
 * @property null|array $cashFlows
 * @property null|array $cashFLowsData
 *
 * @property CompanyCostItem[] $incomeCostItems
 * @property CompanyCostItem[] $expenseCostItems
 */
class ReportForm extends Model
{
    public $from;
    public $to;
    public $cash = null;
    public $cost_item = null;
    public $division = null;
    public $showDetailing = false;
    public $showOnlyCategories = true;
    public $payments;

    private $_cashFlows = null;
    private $_cashFlowsData = null;
    private $_incomeCostItems = null;
    private $_expenseCostItems = null;
    private $_period = null;
    private $_estimatedOrders = null;

    /**
     *
     */
    public function init()
    {
        $this->to   = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime("-6 days"));
    }

    /**
     * @property integer $from
     * @property integer $to
     * @property integer $difference
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'string'],
            [['cash', 'cost_item', 'division', 'payments'], 'integer'],
            [['showDetailing', 'showOnlyCategories'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'from'          => Yii::t('app', 'From'),
            'to'            => Yii::t('app', 'To'),
            'cash'          => Yii::t('app', 'Cash'),
            'showDetailing' => Yii::t('app', 'Show detailing'),
            'showOnlyCategories' => Yii::t('app', 'Показать только категории'),
            'payments' => Yii::t('app', 'Способы оплат'),
        ];
    }

    /**
     * @return mixed
     */
    public function getStartBalance()
    {
        $cashflowIncome = CompanyCashflow::find()
            // ->company()
            ->active()
            ->income()
            ->permittedDivisions()
            ->until($this->from)
            ->cash($this->cash)
            ->costItem($this->cost_item)
            ->division($this->division)
            ->payment($this->payments)
            ->sum('{{%company_cashflows}}.value');

        $cashflowExpense = CompanyCashflow::find()
            // ->company()
            ->active()
            ->expense()
            ->permittedDivisions()
            ->until($this->from)
            ->cash($this->cash)
            ->costItem($this->cost_item)
            ->division($this->division)
            ->payment($this->payments)
            ->sum('{{%company_cashflows}}.value');

        $cashInitBalance = CompanyCash::find()
            // ->company()
            ->division()
            ->active()
            ->andFilterWhere(['id' => $this->cash])
            ->sum('init_money');

        return $cashInitBalance + $cashflowIncome - $cashflowExpense;
    }

    /**
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getCashFlows()
    {
        if (!$this->_cashFlows) {
            $query = CompanyCashflow::find()
                ->joinWith('costItem')
                ->active()
                ->permittedDivisions()
                ->cash($this->cash)
                ->costItem($this->cost_item)
                ->payment($this->payments)
                ->division($this->division)
                ->range($this->from, (new \DateTime($this->to))->modify("+1 day")->format("Y-m-d"))
                ->orderBy('cost_item_type ASC');
            $this->_cashFlows = $query->all();
        }

        return $this->_cashFlows;
    }

    /**
     * @return null
     */
    public function getCashFlowsData()
    {
        if (!$this->_cashFlowsData) {

            $service_cost_item_id = CompanyCostItem::find()
                ->company()
                ->select('id')
                ->isService()
                ->orderBy('id')
                ->scalar();

            foreach ($this->cashFlows as $cashFlow) {
                $cost_item_id = $cashFlow->cost_item_id;
                $date = Yii::$app->formatter->asDate($cashFlow->date, "php:Y-m-d");

                if (!isset($this->_cashFlowsData[$cost_item_id][$date])) {
                    $this->_cashFlowsData[$cost_item_id][$date]['cash'] = 0;
                    $this->_cashFlowsData[$cost_item_id][$date]['not_cash'] = 0;
                }

                if (($cashFlow->order || $cashFlow->costItem->isDepositTransaction()) && !$this->showDetailing) {
                    $this->calculateOrderCashflowNet($cashFlow, $service_cost_item_id);
                } else {
                    if (empty($cashFlow->payments)) {
                        $this->_cashFlowsData[$cost_item_id][$date]['cash'] += $cashFlow->value;
                    } else {
                        foreach ($cashFlow->payments as $payment) {
                            if (!$payment->payment->isAccountable()) {
                                continue;
                            }
                            if ($payment->payment_id == 1) {
                                $this->_cashFlowsData[$cost_item_id][$date]['cash'] += $payment->value;
                            } else {
                                $this->_cashFlowsData[$cost_item_id][$date]['not_cash'] += $payment->value;
                            }
                        }
                    }
                }
            }

            $summary = [];
            if (!empty($this->_cashFlowsData)) {
                foreach ($this->_cashFlowsData as $cost_item_id => $cashFlowData) {
                    $costItem = CompanyCostItem::findOne($cost_item_id);
                    foreach ($cashFlowData as $date => $paymentTypes) {
                        foreach ($paymentTypes as $paymentType => $amount) {
                            if ( ! isset($summary[$costItem->type][$date][$paymentType])) {
                                $summary[$costItem->type][$date][$paymentType] = 0;
                            }
                            $summary[$costItem->type][$date][$paymentType] += $amount;
                        }
                    }
                }
            }

            $this->_cashFlowsData["total"] = $summary;
        }

        return $this->_cashFlowsData;
    }

    /**
     * @param CompanyCashflow $cashFlow
     * @param int $cost_item_id
     */
    private function calculateOrderCashflowNet(CompanyCashflow $cashFlow, int $cost_item_id)
    {
        $cost_item_id = $cashFlow->costItem->isSale() ? $cashFlow->cost_item_id : $cost_item_id;

        $date = Yii::$app->formatter->asDate($cashFlow->date, "php:Y-m-d");

        foreach ($cashFlow->payments as $payment) {
            if (!$payment->payment->isAccountable()) {
                continue;
            }
            $key = $payment->payment_id == 1 ? 'cash' : 'not_cash';

            if (!isset($this->_cashFlowsData[$cost_item_id][$date][$key])) {
                $this->_cashFlowsData[$cost_item_id][$date][$key] = 0;
            }

            if ($cashFlow->costItem->isIncome()) {
                $this->_cashFlowsData[$cost_item_id][$date][$key] += $payment->value;
            } else {
                $this->_cashFlowsData[$cost_item_id][$date][$key] -= $payment->value;
            }
        }
    }

    /**
     * @param $cashFlow
     */
    private function calculateOrderCashflow(CompanyCashflow $cashFlow)
    {
        $cost_item_id = $cashFlow->cost_item_id;
        $date = Yii::$app->formatter->asDate($cashFlow->date, "php:Y-m-d");

        foreach ($cashFlow->payments as $payment) {
            if ($payment->payment_id == 1) {
                $this->_cashFlowsData[$cost_item_id][$date]['cash'] += $payment->value;
            } else {
                $this->_cashFlowsData[$cost_item_id][$date]['not_cash'] += $payment->value;
            }
        }
    }

    /**
     * @return CompanyCostItem[]
     */
    public function getIncomeCostItems()
    {
        if (!$this->_incomeCostItems) {
            $this->_incomeCostItems = CompanyCostItem::find()
                ->company()
                ->permitted()
                ->income()
                ->andFilterWhere([
                    '{{%company_cost_items}}.id' => $this->cost_item
                ])
                ->orderBy('category_id')
                ->all();
        }

        return $this->_incomeCostItems;
    }

    /**
     * @return null
     */
    public function getExpenseCostItems()
    {
        if ( ! $this->_expenseCostItems) {
            $this->_expenseCostItems = CompanyCostItem::find()
                ->company()
                ->permitted()
                ->expense()
                ->andFilterWhere([
                    '{{%company_cost_items}}.id' => $this->cost_item
                ])
                ->orderBy('category_id')
                ->all();
        }

        return $this->_expenseCostItems;
    }

    /**
     * @return \DatePeriod|null
     */
    public function getPeriod()
    {
        if (!$this->_period) {
            $begin         = new \DateTime($this->from);
            $end           = (new \DateTime($this->to))->modify("+1 day");
            $interval      = \DateInterval::createFromDateString('1 day');
            $this->_period = new \DatePeriod($begin, $interval, $end);
        }
        return $this->_period;
    }

    /**
     * @return mixed
     */
    public function getDateDifference()
    {
        $datetimeTo   = (new \DateTime($this->to))->modify("+1 day");
        $datetimeFrom = new \DateTime($this->from);
        $difference   = $datetimeTo->diff($datetimeFrom)->days;
        return $difference;
    }

    /**
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function export()
    {
        $expenseRowNum = null;
        $incomeRowNum = null;
        $rowCount = 1;

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
        $ews = $ea->getSheet(0);
        $ews->setTitle('Финансовый отчет');

        $data[] = $this->getHeader();
        $rowCount++;

        $data[] = $this->getStartRow();
        $rowCount++;

        if (!empty($this->incomeCostItems)) {
            $incomeRowNum = $rowCount;
            $incomeRow[0] = Yii::t('app', 'CostItem Income');
            $total = 0;
            foreach ($this->period as $dt) {
                $type        = CompanyCostItem::TYPE_INCOME;
                $date        = $dt->format("Y-m-d");
                $value       = $this->cashFlowsData["total"][$type][$date] ?? "0";
                if (is_array($value)) {
                    $value = array_reduce($value, function($result, $item) {
                        return $result + $item;
                    }, 0);
                }
                $incomeRow[] = $value;
                $total += $value;
            }
            $incomeRow[] = $total;
            $data[] = $incomeRow;
            $rowCount++;

            foreach ($this->incomeCostItems as $costItem) {
                $row[0] = $costItem->is_deletable ? $costItem->name : Yii::t('app', $costItem->name);
                $total = 0;
                foreach ($this->period as $key => $dt) {
                    $date  = $dt->format("Y-m-d");
                    $value = $this->cashFlowsData[$costItem->id][$date] ?? "0";
                    if (is_array($value)) {
                        $value = array_reduce($value, function($result, $item) {
                            return $result + $item;
                        }, 0);
                    }
                    $row[] = $value;
                    $total += $value;
                }
                $row[] = $total;
                $data[] = $row;
                $rowCount++;
                unset($row);
            }
        }

        if (!empty($this->expenseCostItems)) {
            $expenseRowNum = $rowCount;
            $expenseRow[0] = Yii::t('app', 'CostItem Expense');
            $total = 0;
            foreach ($this->period as $dt) {
                $type         = CompanyCostItem::TYPE_EXPENSE;
                $date         = $dt->format("Y-m-d");
                $value        = $this->cashFlowsData["total"][$type][$date] ?? "0";
                if (is_array($value)) {
                    $value = array_reduce($value, function($result, $item) {
                        return $result + $item;
                    }, 0);
                }
                $expenseRow[] = $value;
                $total += $value;
            }
            $expenseRow[] = $total;
            $data[] = $expenseRow;
            $rowCount++;

            foreach ($this->expenseCostItems as $costItem) {
                $row[0] = $costItem->is_deletable ? $costItem->name : Yii::t('app', $costItem->name);
                $total = 0;
                foreach ($this->period as $key => $dt) {
                    $date  = $dt->format("Y-m-d");
                    $value = $this->cashFlowsData[$costItem->id][$date] ?? "0";
                    if (is_array($value)) {
                        $value = array_reduce($value, function($result, $item) {
                            return $result + $item;
                        }, 0);
                    }
                    $row[] = $value;
                    $total += $value;
                }
                $row[] = $total;
                $data[] = $row;
                $rowCount++;
                unset($row);
            }
        }

        $data[] = $this->getBalanceRow();
        $rowCount++;

        $data[] = $this->getEndRow();
        $rowCount++;

        $ews->fromArray($data, ' ', 'A1');

        $first_letter = \PHPExcel_Cell::stringFromColumnIndex(0);
        $last_letter  = \PHPExcel_Cell::stringFromColumnIndex($this->dateDifference + 1);

        $ews->getColumnDimension('A')->setAutoSize(true);
        foreach ($ea->getAllSheets() as $sheet) {
            // Iterating through all the columns
            // The after Z column problem is solved by using numeric columns; thanks to the columnIndexFromString method
            for ($col = 0; $col <= \PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn()); $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        $ea->getActiveSheet()->getStyle("{$first_letter}1:{$last_letter}1")->getFont()->setBold(true);

        $ea->getActiveSheet()->getStyle("{$first_letter}2:{$last_letter}2")->getFont()->setBold(true);
        if ($incomeRowNum)
            $ea->getActiveSheet()->getStyle("{$first_letter}{$incomeRowNum}:{$last_letter}{$incomeRowNum}")->getFont()->setBold(true);
        if ($expenseRowNum)
            $ea->getActiveSheet()->getStyle("{$first_letter}{$expenseRowNum}:{$last_letter}{$expenseRowNum}")->getFont()->setBold(true);

        $balanceRowNum = $rowCount - 2;
        $endRowNum = $rowCount - 1;
        $ea->getActiveSheet()->getStyle("{$first_letter}{$balanceRowNum}:{$last_letter}{$balanceRowNum}")->getFont()->setBold(true);
        $ea->getActiveSheet()->getStyle("{$first_letter}{$endRowNum}:{$last_letter}{$endRowNum}")->getFont()->setBold(true);

        $filename = "Финансовый_Отчет_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        header('Content-Type: application/vnd.ms-excel');
        $objWriter = \PHPExcel_IOFactory::createWriter($ea, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        $header[0] = Yii::t('app', 'Cost Item');
        foreach ($this->period as $dt) {
            $header[] = Yii::$app->formatter->asDate($dt, 'php:d F');
        }
        $header[] = "Итого";
        return $header;
    }

    /**
     * @return array
     */
    private function getStartRow()
    {
        $previousBalance = $this->getStartBalance();
        $row[0] = "Остаток на начало дня";
        foreach ($this->period as $key => $dt) {
            $row[] = $previousBalance;
            $date  = $dt->format("Y-m-d");
            $income = $this->cashFlowsData["total"][CompanyCostItem::TYPE_INCOME][$date] ?? 0;
            $expense = $this->cashFlowsData["total"][CompanyCostItem::TYPE_EXPENSE][$date] ?? 0;

            if (is_array($income)) {
                $income = array_reduce($income, function($result, $item) {
                    return $result + $item;
                }, 0);
            }
            if (is_array($expense)) {
                $expense = array_reduce($expense, function($result, $item) {
                    return $result + $item;
                }, 0);
            }

            $previousBalance = $previousBalance + $income - $expense;
        }
        $row[] = $previousBalance;
        return $row;
    }

    /**
     * @return array
     */
    private function getBalanceRow()
    {
        $row[0] = "Итого";
        $total = 0;
        foreach ($this->period as $dt) {
            $date  = $dt->format("Y-m-d");
            $income = $this->cashFlowsData["total"][CompanyCostItem::TYPE_INCOME][$date] ?? 0;
            $expense = $this->cashFlowsData["total"][CompanyCostItem::TYPE_EXPENSE][$date] ?? 0;
            if (is_array($income)) {
                $income = array_reduce($income, function($result, $item) {
                    return $result + $item;
                }, 0);
            }
            if (is_array($expense)) {
                $expense = array_reduce($expense, function($result, $item) {
                    return $result + $item;
                }, 0);
            }
            $row[] = $income - $expense;
            $total += $income - $expense;
        }
        $row[] = $total;
        return $row;
    }

    /**
     * @return array
     */
    private function getEndRow()
    {
        $previousBalance = $this->getStartBalance();
        $row[0] = Yii::t('app', 'CostItem Remainder');
        foreach ($this->period as $key => $dt) {
            $date  = $dt->format("Y-m-d");
            $income = $this->cashFlowsData["total"][CompanyCostItem::TYPE_INCOME][$date] ?? 0;
            $expense = $this->cashFlowsData["total"][CompanyCostItem::TYPE_EXPENSE][$date] ?? 0;

            if (is_array($income)) {
                $income = array_reduce($income, function($result, $item) {
                    return $result + $item;
                }, 0);
            }
            if (is_array($expense)) {
                $expense = array_reduce($expense, function($result, $item) {
                    return $result + $item;
                }, 0);
            }

            $previousBalance = $previousBalance + $income - $expense;
            $row[] = $previousBalance;
        }
        $row[] = $previousBalance;
        return $row;
    }

    public function formName()
    {
        return '';
    }
}

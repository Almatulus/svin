<?php

namespace core\forms\finance;

use core\models\finance\CompanyCashflow;

class CashflowUpdateForm extends CashflowForm
{
    protected $cashflow;

    const SCENARIO_UPDATE_DATE = 'update-date';

    /**
     * CashflowUpdateForm constructor.
     * @param int $id
     * @param int $user_id
     * @param array $config
     * @internal param CompanyCashflow $cashflow
     */
    public function __construct(int $id, int $user_id, $config = [])
    {
        $this->cashflow = CompanyCashflow::findOne($id);

        parent::__construct($user_id, $config);

        if ($this->cashflow) {
            $this->attributes = $this->cashflow->attributes;
            $this->date = (new \DateTime($this->cashflow->date))->format("Y-m-d H:i");

            if (!$this->cashflow->isEditable()) {
                $this->scenario = self::SCENARIO_UPDATE_DATE;
            }

            foreach ($this->cashflow->payments as $payment) {
                $this->payments[$payment->payment_id]['value'] = $payment->value;
            }
        }
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE_DATE] = ['date', 'payments'];
        return $scenarios;
    }

    /**
     * @return null|CompanyCashflow
     */
    public function getCashflow()
    {
        return $this->cashflow;
    }
}

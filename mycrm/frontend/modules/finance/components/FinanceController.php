<?php

namespace frontend\modules\finance\components;

use yii\web\Controller;

/**
 * CashController implements the CRUD actions for CompanyCash model.
 */
class FinanceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->getView()->params['sideNavView']    = 'list';
            $this->getView()->params['sideNavID']      = 'finance';

            return true;
        }

        return false;
    }

}

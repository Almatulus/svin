<?php

namespace frontend\modules\customer\components;

use yii\web\Controller;

/**
 * CashController implements the CRUD actions for CompanyCash model.
 */
class CustomerModuleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->getView()->params['sideNavView']    = 'list';
            $this->getView()->params['sideNavID']      = 'customers';

            return true;
        }

        return false;
    }

}

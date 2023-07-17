<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\OptionsTrait;
use core\forms\customer\SmscCallbackForm;
use core\services\customer\CustomerRequestService;
use Yii;
use yii\rest\ActiveController;

class RequestController extends ActiveController
{
    public $modelClass = 'core\models\customer\CustomerRequest';

    use OptionsTrait;

    private $service;

    public function __construct($id, $module, CustomerRequestService $customerRequestService, $config = [])
    {
        $this->service = $customerRequestService;
        parent::__construct($id, $module, $config = []);
    }

    public function beforeAction($event)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($event);
    }

    public function actionSmscCallback()
    {
        $form = new SmscCallbackForm();
        if($form->load(Yii::$app->request->post()) && $form->validate()){
            $this->service->processCallback($form);
        }
    }
}

<?php

namespace api\modules\v2\controllers\company;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\company\CompanyInsuranceSearch;
use core\forms\insurance\InsuranceForm;
use core\services\company\InsuranceService;
use Yii;
use yii\filters\AccessControl;

class InsuranceController extends BaseController
{
    public $modelClass = 'core\models\InsuranceCompany';

    /** @var InsuranceService */
    private $insuranceService;

    /**
     * InsuranceController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param InsuranceService $insuranceService
     * @param array $config
     */
    public function __construct($id, $module, InsuranceService $insuranceService, $config = [])
    {
        $this->insuranceService = $insuranceService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'update',
                        'options',
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['view']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new CompanyInsuranceSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @return InsuranceForm|array
     * @throws \Exception
     */
    public function actionUpdate()
    {
        $form = new InsuranceForm();

        if ($form->load(Yii::$app->request->bodyParams, "") && $form->validate()) {
            $this->insuranceService->mapInsuranceCompanies(
                Yii::$app->user->identity->company_id,
                $form->companies
            );

            return $form->attributes;
        }

        return $form;
    }

}

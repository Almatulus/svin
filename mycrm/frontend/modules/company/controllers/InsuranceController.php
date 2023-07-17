<?php

namespace frontend\modules\company\controllers;

use core\forms\insurance\InsuranceForm;
use core\repositories\exceptions\NotFoundException;
use core\services\company\InsuranceService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * InsuranceController implements the CRUD actions for Insurance model.
 */
class InsuranceController extends Controller
{
    private $insuranceService;

    public function __construct($id, $module, InsuranceService $insuranceService, $config = [])
    {
        $this->insuranceService = $insuranceService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => ['insuranceCompanyAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Insurance models.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionIndex()
    {
        if ( ! Yii::$app->user->identity->company->isMedCategory()) {
            throw new NotFoundException('Page not found');
        }

        $form = new InsuranceForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $this->insuranceService->mapInsuranceCompanies(
                Yii::$app->user->identity->company_id,
                $form->companies
            );

            Yii::$app->session->setFlash(
                'success',
                Yii::t('app', 'Successful saving')
            );

            return $this->redirect(['index']);
        }

        return $this->render('index', [
            "model" => $form
        ]);
    }
}

<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use core\forms\company\CompanyUpdateForm;
use core\models\company\Company;
use core\services\company\CompanyService;
use core\services\dto\CompanyDetailsData;
use core\services\dto\PersonData;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class CompanyController extends BaseController
{
    public $modelClass = 'core\models\company\Company';
    private $companyService;

    public function __construct(
        $id,
        $module,
        CompanyService $companyService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->companyService = $companyService;
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
                    'actions' => ['index', 'update', 'balance', 'options'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @return Company
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        /* @var Company $model */
        $model = Yii::$app->user->identity->company;
//        if ( ! Yii::$app->user->can("companyView", ['model' => $model])) {
//            throw new ForbiddenHttpException('Not allowed');
//        }

        return $model;
    }

    /**
     * @return Company
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate()
    {
        /* @var Company $model */
        $model = Yii::$app->user->identity->company;
        if ( ! Yii::$app->user->can("companyView", ['model' => $model])) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $form = new CompanyUpdateForm($model);
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ( ! $form->validate()) {
            throw new \InvalidArgumentException('Failed to create the object');
        }

        $model = $this->companyService->restrictEdit(
            $model->id,
            new CompanyDetailsData(
                $form->address,
                $form->bank,
                $form->bik,
                $form->bin,
                $form->iik,
                $form->license_issued,
                $form->license_number,
                $form->name,
                $form->phone,
                $form->widget_prefix,
                $form->online_start,
                $form->online_finish,
                $form->logo_id
            ),
            new PersonData(
                $form->head_name,
                $form->head_surname,
                $form->head_patronymic
            ),
            $form->notify_about_order,
            $form->cashback_percent
        );

        return $model;
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionBalance()
    {
        /* @var Company $model */
        $model = Yii::$app->user->identity->company;
        if ( ! Yii::$app->user->can("companyView", ['model' => $model])) {
            throw new ForbiddenHttpException('Not allowed');
        }

        return [
            'tariff'       => $model->tariff,
            'balance'      => $model->getBalance(),
            'sms_limit'    => $model->getSmsLimit(),
            'last_payment' => $model->lastTariffPayment->start_date ?? null,
            'next_payment' => $model->lastTariffPayment->nextPaymentDate ?? null
        ];
    }
}

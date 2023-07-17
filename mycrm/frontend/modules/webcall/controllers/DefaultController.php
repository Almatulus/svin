<?php

namespace frontend\modules\webcall\controllers;

use common\components\Notification;
use core\forms\webcall\WebCallForm;
use core\forms\webcall\WebCallUpdateForm;
use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\order\Order;
use core\models\webcall\WebCall;
use core\models\webcall\WebcallAccount;
use core\services\company\WebCallService;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `webcall` module
 */
class DefaultController extends Controller
{
    private $service;

    public function __construct($id, $module, WebCallService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['settings', 'calls'],
                'rules' => [
                    [
                        'actions' => ['settings', 'calls'],
                        'allow'   => true,
                        'roles'   => ['webcallAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'call-start' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'call-start') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Setup WebCall model.
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionSettings()
    {
        $model = $this->findModel(Yii::$app->user->identity->company_id);

        $dataProvider = new ActiveDataProvider([
            'query' => WebcallAccount::find()->joinWith('division')->andWhere(['company_id' => Yii::$app->user->identity->company_id])
        ]);

        $this->checkAccess($model->company);

        $form = new WebCallUpdateForm($model);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->updateSettings(
                    $model->id,
                    $form->api_key,
                    $form->username,
                    $form->domain
                );
                $model->subscribe();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
            return $this->refresh();
        }

        return $this->render('settings', [
            'model'        => $form,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Get list of calls
     * @return mixed
     */
    public function actionCalls()
    {
        $webcall = $this->findModel(Yii::$app->user->identity->company_id);

        $this->checkAccess($webcall->company);

        $current_date = new \DateTime();
        $model = new WebCallForm($webcall);

        if (!$model->load(Yii::$app->request->get())) {
            $model->from_date = $current_date->modify("-15 days")->format("Y-m-d");
            $model->to_date = $current_date->modify("+1 month")->format("Y-m-d");
            // @TODO Get response from db in later requests
            //$model->setLastCallsList();
        }
        $model->getCallsList();

        return $this->render('calls', [
            'model' => $model,
        ]);
    }

    /**
     * Show call start notification
     * @param integer $company_id
     */
    public function actionCallStart($company_id)
    {
        $webcall = $this->findModel($company_id);

        $this->checkAccess($webcall->company);

        $action = \Yii::$app->request->getBodyParam('webhook');
        $event = \Yii::$app->request->getBodyParam('event');

        if ("call.start" == $action['action'] && $event['direction'] == WebCall::CALL_IN) {
            $companyCustomer = false;
            $phone = preg_replace('/\s+/', '', $event['client_number']);
            if (preg_match('/^\+(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $m)) {
                $customer_phone = "+{$m[1]} {$m[2]} {$m[3]} {$m[4]} {$m[5]}";
                /* @var Customer $customer */
                $customer = Customer::findByPhone($customer_phone);
                if ($customer) {
                    $companyCustomer = CompanyCustomer::find()->company($webcall->company_id)->customer($customer->id)->one();
                }
            }

            if ($companyCustomer) {
                $customer = $companyCustomer->customer;
                $lastVisitDateTime = $companyCustomer->getLastVisitDateTime();
                /* @var Order $lastOrder */
                $lastOrder = $companyCustomer->getLastOrder()->one();
                $message = Yii::t('app', 'Income call from {phone} {name} {category} {discount} {last_visit_date} {last_visit_staff}', [
                    'phone' => $customer->phone,
                    'name' => $customer->getFullName(),
                    'category' => '',
                    'discount' => $companyCustomer->discount,
                    'last_visit_date' => $lastOrder ? Yii::$app->formatter->asDate($lastVisitDateTime) : null,
                    'last_visit_staff' => $lastOrder ? $lastOrder->staff->getFullName() : null,
                ]);
            } else {
                $message = Yii::t('app', 'Income call from {phone} {name}', [
                    'phone' => $event['client_number'],
                    'name' => $event['client_name']
                ]);
            }
            Notification::show($webcall->company->users, $message);
        }
    }

    /**
     * Checks company has access or throw exception
     * @param Company $company
     * @throws NotFoundHttpException
     */
    private function checkAccess(Company $company)
    {
        if (!$company->hasWebCallAccess()) {
            throw new AccessDeniedException('Company has not access');
        }
    }

    /**
     * Returns model
     * @return WebCall
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = WebCall::findOne(['company_id' => $id, 'enabled' => true]);
        if ($model == null) {
            throw new NotFoundHttpException('No web call is set up');
        }
        return $model;
    }
}

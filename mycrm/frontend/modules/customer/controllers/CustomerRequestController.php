<?php

namespace frontend\modules\customer\controllers;

use common\components\Model;
use core\models\customer\CustomerRequest;
use core\models\customer\CustomerRequestTemplate;
use core\services\customer\CustomerRequestService;
use frontend\search\RequestSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CustomerRequestController implements the CRUD actions for CustomerRequest model.
 */
class CustomerRequestController extends Controller
{
    private $service;

    public function __construct(
        $id,
        $module,
        CustomerRequestService $customerRequestService,
        $config = []
    ) {
        $this->service = $customerRequestService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'settings', 'test'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'test'],
                        'allow'   => true,
                        'roles'   => ['customerRequestView'],
                    ],
                    [
                        'actions' => ['settings'],
                        'allow'   => true,
                        'roles'   => ['smsTemplatesAdmin'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CustomerRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RequestSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params, true);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'smsTotal' => $this->service->getTotalSmsCount($dataProvider->getModels()),
            'priceTotal' => $this->service->getTotalPrice($dataProvider->getModels())
        ]);
    }

    /**
     * Displays a single CustomerRequest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the CustomerRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CustomerRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomerRequest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Shows SMS Templates.
     * All SMS Templates are loaded (or generated if not exist) in RequestSettingsForm with function 'loadSettings'.
     * To customize (add/remove) items in SMS Templates, refer to RequestSettingsForm
     * This action is also used to save changes in SMS Templates
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionSettings() {

        $keys = CustomerRequestTemplate::loadTemplates();

        $post = Yii::$app->request->post();
        if($post) {
            if (Model::loadMultiple($keys, $post) && Model::validateMultiple($keys))
            {
                $success = true;
                $transaction = Yii::$app->db->beginTransaction();
                foreach ($keys as $template) {
                    /* @var $template CustomerRequestTemplate */
                    $success = $template->save();
                    if(!$success) break;
                }
                if($success)
                {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app','Successful saving'));
                    return $this->redirect(['settings']);
                }
                else
                {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Error while saving'));
                }
            }
        }


        return $this->render('settings', [
            'keys' => $keys,
        ]);
    }

//    public function actionTest() {
//        echo CustomerRequest::generateRequest(1,CustomerRequestTemplate::TYPE_BIRTHDAY,['%TITLE%' => 'Еркебулан']);
//    }

}

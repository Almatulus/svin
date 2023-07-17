<?php

namespace frontend\modules\admin\controllers;

use core\helpers\GenderHelper;
use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\models\order\OrderPayment;
use core\models\order\OrderService;
use core\models\Payment;
use core\models\Staff;
use core\services\customer\CompanyCustomerService;
use core\services\dto\CustomerInsuranceData;
use core\services\order\OrderModelService;
use DateTime;
use DomainException;
use Exception;
use frontend\modules\admin\forms\OrderImportForm;
use frontend\modules\admin\search\OrderSearch;
use core\models\order\Order;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Default controller for the `admin` module
 */
class OrderController extends Controller
{
    /* @var OrderModelService $service */
    private $service;
    private $companyCustomerService;

    public function __construct($id, $module, OrderModelService $service, CompanyCustomerService $companyCustomerService, $config = [])
    {
        $this->service = $service;
        $this->companyCustomerService = $companyCustomerService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'import'],
                        'allow' => true,
                        'roles' => ['userView'],
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $searchModel->start = date('Y-m-d');
        $searchModel->end = date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title'=> "Order #".$id,
                'content'=>$this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
            ];
        }else{
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

<?php

namespace frontend\modules\order\controllers;

use core\models\order\Order;
use core\services\order\OrderModelService;
use frontend\modules\order\search\OrderSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `order` module
 *
 * @var OrderModelService $orderService
 */
class OrderController extends Controller
{
    private $orderService;

    public function __construct(
        $id,
        $module,
        OrderModelService $orderService,
        $config = []
    ) {
        $this->orderService = $orderService;
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
                        'roles'   => ['orderView'],
                    ],
                    [
                        'actions' => ['cancel'],
                        'allow'   => true,
                        'roles'   => ['orderCreate'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow'   => true,
                        'roles'   => ['@'],
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
     * Lists all Order models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel            = new OrderSearch();
        $params = Yii::$app->request->queryParams;
        if (!isset($params['from_date'])) {
            $params['from_date'] =
                (new \DateTime())->modify("-6 days")->format("Y-m-d");
        }
        if (!isset($params['to_date'])) {
            $params['to_date'] =
                (new \DateTime())->format("Y-m-d");
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCancel()
    {
        $selected = Yii::$app->request->post("selection");

        try {
            $orders = Order::findAll(['id' => $selected]);
            foreach ($orders as $key => $order) {
                $this->orderService->reset($order->id);
            }
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful saving'));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash("error",
                Yii::t("app", "Error while returning order: {error_text}",
                    ['error_text' => $e->getMessage()]));
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Returns file with total stats
     *
     * @param int $mode
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function actionExport($mode = 0)
    {
        $session       = Yii::$app->session;
        $search_params = $session->get('order_search_query');
        if ($mode == 1) {
            $search_params = null;
        }

        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($search_params);

        $this->orderService->export($dataProvider);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
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

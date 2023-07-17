<?php

namespace frontend\modules\medCard\controllers;

use core\forms\medCard\MedCardTabForm;
use core\helpers\medCard\MedCardToothHelper;
use core\models\medCard\MedCard;
use core\models\medCard\MedCardTab;
use core\models\medCard\MedCardTooth;
use core\services\medCard\dto\MedCardTabCommentData;
use core\services\medCard\MedCardModelService;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ToothData;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `medCard` module
 */
class DefaultController extends Controller
{
    private $medCardService;

    public function __construct(
        $id,
        $module,
        MedCardModelService $medCardService,
        $config = []
    ) {
        $this->medCardService = $medCardService;
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
                'only'  => ['index', 'create', 'update', 'delete', 'history'],
                'rules' => [
                    [
                        'actions' => [
                            'create',
                            'update',
                            'delete',
                            'history'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ]
            ]
        ];
    }

    /**
     * @param $order_id
     *
     * @return mixed
     */
    public function actionCreate($order_id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new MedCardTabForm();

        $form->load(Yii::$app->request->post());
        if ( ! $form->validate()) {
            return ['errors' => $form->firstErrors];
        }

        try {
            $medCardTab = $this->medCardService->createTab(
                Yii::$app->user->id,
                Yii::$app->user->identity->company_id,
                $order_id,
                $this->getTeeth($form->teeth),
                $this->getComments($form->comments),
                $this->getServiceData($form->services),
                $form->diagnosis_id
            );

            // refresh to fetch number of medcard, which is created in database
            $medCardTab->medCard->refresh();

            return [
                'medCard' => $medCardTab->medCard,
                'tab'     => $medCardTab
            ];
        } catch (\DomainException $e) {
            return ['errors' => ['teeth' => $e->getMessage()]];
        }
    }

    /**
     * @param integer $tab_id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($tab_id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findTab($tab_id);

        $form = new MedCardTabForm();
        $form->load(Yii::$app->request->post());
        if ( ! $form->validate()) {
            return ['errors' => $form->errors];
        }

        try {
            $medCardTab = $this->medCardService->editTab(
                Yii::$app->user->id,
                Yii::$app->user->identity->company_id,
                $model->id,
                $this->getTeeth($form->teeth),
                $this->getComments($form->comments),
                $this->getServiceData($form->services),
                $form->diagnosis_id
            );

            // refresh to fetch number of medcard, which is created in database
            $medCardTab->medCard->refresh();

            return ['tab' => $medCardTab];
        } catch (\DomainException $e) {
            return ['errors' => ['teeth' => $e->getMessage()]];
        }
    }

    /**
     * @param $tab_id
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($tab_id)
    {
        $this->medCardService->deleteTab($this->findTab($tab_id)->id);

        return Json::encode(['message' => Yii::t('app', 'Successful deleted')]);
    }

    public function actionHistory($teeth, $company_customer_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $medCardTeeth = MedCardTooth::find()
                                    ->joinWith([
                                        'medCardTab.medCard.order',
                                        'medCardTabTeethDiagnosis'
                                    ])
                                    ->where([
                                        '{{%orders}}.company_customer_id' => $company_customer_id,
                                        '{{%med_card_tooth}}.teeth_num'   => $teeth,
                                    ])
                                    ->orderBy(['{{%orders}}.created_time' => SORT_DESC])
                                    ->all();
        return $medCardTeeth;
    }

    /**
     * Finds the MedCard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return MedCard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MedCard::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     *
     * @return MedCardTab
     * @throws NotFoundHttpException
     */
    protected function findTab($id)
    {
        if (($model = MedCardTab::findOne($id)) !== null) {
            /** @var MedCardTab $model */
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param array $comments
     *
     * @return MedCardTabCommentData[]
     */
    private function getComments($comments)
    {
        if ( ! is_array($comments)) {
            return [];
        }

        $comments = array_filter($comments, function ($comment) {
            return ! empty($comment);
        });

        $result = [];
        foreach ($comments as $category_id => $comment) {
            $result[] = new MedCardTabCommentData($comment, $category_id);
        }

        return $result;
    }

    /**
     * @param $teeth
     *
     * @return ToothData[]
     * @internal param $teeth
     */
    private function getTeeth($teeth)
    {
        if ( ! is_array($teeth)) {
            return [];
        }

        $teeth = array_filter($teeth, function ($tooth) {
            return ! empty($tooth['diagnosis_id']);
        });

        $result = [];
        foreach ($teeth as $teeth_num => $tooth) {
            $result[] = new ToothData(
                $teeth_num,
                MedCardToothHelper::getType(intval($teeth_num)),
                $tooth['diagnosis_id'],
                ! empty($tooth['mobility']) ? intval($tooth['mobility']) : null
            );
        }

        return $result;
    }

    /**
     * Returns OrderServiceData array from form data
     *
     * @param array $services
     *
     * @return OrderServiceData[]
     */
    private function getServiceData($services)
    {
        $services = $services ?: [];
        return array_map(function ($service) {
            return new OrderServiceData(
                $service['division_service_id'],
                $service['price'],
                null,
                $service['discount'],
                $service['quantity']
            );
        }, $services);
    }
}

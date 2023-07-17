<?php

namespace frontend\modules\admin\controllers;

use core\models\customer\CustomerRequest;
use core\models\Staff;
use frontend\modules\admin\search\StaffSearch;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `admin` module
 */
class StaffController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'services', 'list'],
                        'allow'   => true,
                        'roles'   => ['userView'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                    [
                        'actions' => ['sms'],
                        'allow'   => true,
                        'roles'   => ['administrator']
                    ]
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'sms' => ['post'],
                ],
            ]
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionServices()
    {
        $staffs = Staff::find()
            ->leftJoin("{{%staff_division_service_map}}", '{{%staff_division_service_map}}.staff_id = crm_staffs.id')
            ->where("{{%staff_division_service_map}}.staff_id IS NULL")
            ->andWhere("status != :fired", [":fired" => Staff::STATUS_FIRED])
            ->all();
        return $this->render('services', [
            'staffs' => $staffs,
        ]);
    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionList()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return array
     */
    public function actionSms()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new DynamicModel(['selected', 'message']);
        $form->addRule(['selected', 'message'], 'required');
        $form->addRule(['message'], 'string', ['max' => '300']);
        $form->addRule(['selected'], 'each', ['rule' => ['integer']]);
        $form->addRule(['selected'], 'each',
            ['rule' => ['exist', 'targetClass' => Staff::class, 'targetAttribute' => 'id']]);

        $form->attributes = Yii::$app->request->bodyParams;

        if ($form->validate()) {
            /** @var Staff[] $staffs */
            $staffs = Staff::find()->where(['id' => $form->selected])->all();

            foreach ($staffs as $staff) {
                CustomerRequest::sendNotAssignedSMS($staff->phone, $form->message);
            }
            return ['status' => 'success', 'message' => "SMS успешно отправлены"];
        }

        return ['errors' => $form->firstErrors];
    }
}

<?php

namespace api\modules\v2\controllers\staff;

use core\models\StaffReview;
use core\forms\staff\ReviewCreateForm;
use api\modules\v2\search\staff\ReviewSearch;
use core\forms\staff\ReviewUpdateForm;
use api\modules\v2\OptionsTrait;
use core\repositories\exceptions\AlreadyExistsException;
use core\services\StaffReviewService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ReviewController extends ActiveController
{
    public $modelClass = 'core\models\StaffReview';

    private $staffReviewService;

    public function __construct($id, $module, StaffReviewService $staffReviewService, $config = [])
    {
        $this->staffReviewService = $staffReviewService;
        parent::__construct($id, $module, $config = []);
    }

    use OptionsTrait;

    public function beforeAction($event)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($event);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['only'] = ['create', 'update'];
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
            QueryParamAuth::className(),
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['create', 'update'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    // TODO add actions in APIary
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * @param integer $id staff id
     * @return StaffReview
     * @throws BadRequestHttpException
     */
    public function actionCreate($id)
    {
        throw new ForbiddenHttpException();

        $form = new ReviewCreateForm();
        $form->load(Yii::$app->getRequest()->getBodyParams());
        $form->staff_id = $id;
        $form->customer_id = Yii::$app->user->id;

        if (!$form->validate()) {
            throw new \InvalidArgumentException('Failed to create the object');
        }

        try {
            $model = $this->staffReviewService->add(
                $form->customer_id,
                $form->staff_id,
                $form->value,
                $form->comment
            );
        } catch (AlreadyExistsException $e) {
            throw new BadRequestHttpException('Review already exists');
        }

        Yii::$app->getResponse()->setStatusCode(201);

        return $model;
    }

    public function actionUpdate($id)
    {
        throw new ForbiddenHttpException();

        $form = new ReviewUpdateForm();
        $form->load(Yii::$app->getRequest()->getBodyParams());

        if (!$form->validate()) {
            throw new BadRequestHttpException('Failed to create the object');
        }

        $model = $this->findModel($id, Yii::$app->user->id);
        $this->staffReviewService->edit(
            $model->customer_id,
            $model->staff_id,
            $form->value,
            $form->comment
        );

        return $this->findModel($id, Yii::$app->user->id);
    }

    public function prepareDataProvider()
    {
        $searchModel = new ReviewSearch([
            'staff_id' => Yii::$app->request->getQueryParam('id'),
        ]);
        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param integer $staff_id
     * @param integer $customer_id
     * @return StaffReview
     * @throws NotFoundHttpException
     */
    protected function findModel($staff_id, $customer_id)
    {
        /* @var StaffReview $model */
        if (($model = StaffReview::find()->staff($staff_id)->customer($customer_id)->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
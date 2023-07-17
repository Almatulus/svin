<?php

namespace frontend\controllers;

use core\forms\staff\ScheduleTemplateForm;
use core\forms\staff\StaffCreateForm;
use core\forms\staff\StaffUpdateForm;
use core\models\Image;
use core\models\order\Order;
use core\models\Staff;
use core\services\staff\dto\ScheduleTemplateData;
use core\services\staff\dto\TemplateIntervalData;
use core\services\staff\ScheduleTemplateService;
use core\services\staff\StaffModelService;
use core\services\StaffScheduleService;
use frontend\search\StaffSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * StaffController implements the CRUD actions for Staff model.
 */
class StaffController extends Controller
{
    /**
     * @var StaffModelService
     */
    private $staffService;

    /** @var StaffScheduleService */
    private $scheduleService;
    /** @var ScheduleTemplateService */
    private $scheduleTemplateService;

    /**
     * StaffController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param StaffModelService $staffService
     * @param StaffScheduleService $scheduleService
     * @param ScheduleTemplateService $scheduleTemplateService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        StaffModelService $staffService,
        StaffScheduleService $scheduleService,
        ScheduleTemplateService $scheduleTemplateService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->staffService = $staffService;
        $this->scheduleService = $scheduleService;
        $this->scheduleTemplateService = $scheduleTemplateService;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'update',
                            'create',
                            'delete',
                            'disable-calendar',
                            'archive',
                            'restore',
                            'schedule'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['search'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ]
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete'  => ['post'],
                    'restore' => ['post']
                ],
            ],
        ];
    }

    /**
     * Lists all Staff models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionArchive()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);

        return $this->render('archive', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Staff model.
     * If creation is successful, the browser will be redirected to the 'update' page.
     *
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new StaffCreateForm(Yii::$app->user->identity->company_id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $imageFile = UploadedFile::getInstance($model, 'image_file');
            $image = $imageFile ? Image::uploadImage($imageFile) : null;

            try {
                $staff = $this->staffService->hire(
                    Yii::$app->user->identity->company_id,
                    $model,
                    $image
                );

                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));

                $action = Yii::$app->request->post('action');
                if ($action == 'add-another') {
                    return $this->redirect(['create']);
                }

                return $this->redirect(['update', 'id' => $staff->id]);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', Yii::t('app', $e->getMessage()));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Renders an existing Staff model.
     * If model is not found throws 404 error
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->where(['staff_id' => $model->id]),
            'sort'  => ['defaultOrder' => ['datetime' => SORT_DESC]]
        ]);

        return $this->render('view', ['model' => $model, 'dataProvider' => $dataProvider]);
    }

    /**
     * Updates an existing Staff model.
     * If update is successful, the browser will be redirected to the 'update' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $staff = $this->findModel($id);

        $model = new StaffUpdateForm($staff);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $imageFile = UploadedFile::getInstance($model, 'image_file');
            $image     = $imageFile ? Image::uploadImage($imageFile) : null;

            try {
                $this->staffService->edit(
                    $staff->id,
                    Yii::$app->user->identity->company_id,
                    $model,
                    $image
                );

                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );

                $action = Yii::$app->request->post('action');
                if ($action == 'add-another') {
                    return $this->redirect(['create']);
                }

                return $this->redirect(['update', 'id' => $staff->id]);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t('app', $e->getMessage())
                );
            }
        }

        return $this->render('update', [
            'model' => $model,
            'staff' => $staff
        ]);
    }

    /**
     * Deletes an existing Staff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $this->staffService->fire($id);
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful delete {something}', ['something' => $model->getFullName()])
            );
            return $this->redirect('index');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id);

        try {
            $this->staffService->restore($id, Yii::$app->user->identity->company_id);
            return $this->redirect(['index']);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        $url = Yii::$app->request->referrer;
        if (!$url) {
            $url = ['view', 'id' => $id];
        }

        return $this->redirect($url);
    }

    /**
     * Lists staff by filtering
     *
     * @return array
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Ajax staffs list
        $division_params = Yii::$app->request->post('depdrop_parents', null);

        if ($division_params === null || ! $division_params[0]) {
            return ['output' => '', 'selected' => ''];
        }

        $selected = [];
        /* @var Staff[] $staffs */
        $staffs = Staff::find()
            ->enabled()
            ->timetableVisible()
            ->division($division_params[0])
            ->all();

        $out = array_map(function (Staff $staff) {
            return [
                'id'   => $staff->id,
                'name' => $staff->getFullName()
            ];
        }, $staffs);

        $params = Yii::$app->request->post('depdrop_params', null);
        if ($params != null && $params[0]) {
            $selected = "" . $params[0];
        }

        return ['output' => $out, 'selected' => $selected];
    }

    /**
     * @param $id
     * @param null $division_id
     * @return string|Response
     */
    public function actionSchedule($id, $division_id = null)
    {
        $model = $this->findModel($id);
        $division_ids = $model->getDivisions()->select('id')->column();

        if (!$division_id || !in_array($division_id, $division_ids)) {
            $division_id = current($division_ids);
        }

        $form = new ScheduleTemplateForm();
        $form->setCompanyId(Yii::$app->user->identity->company_id);
        $form->division_id = $division_id;
        $template = $model->getScheduleTemplate($division_id);
        if ($template) {
            $form->setTemplate($template);
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $templateData = new ScheduleTemplateData(
                $id,
                $form->division_id,
                $form->interval_type,
                $form->type
            );

            $intervalData = array_map(function ($day, $intervalData) {
                return new TemplateIntervalData(
                    $day,
                    $intervalData['start'],
                    $intervalData['end'],
                    $intervalData['break_start'] ?? null,
                    $intervalData['break_end'] ?? null
                );
            }, array_keys($form->intervals), $form->intervals);

            $this->scheduleTemplateService->generate($templateData, $intervalData, new \DateTime($form->start));

            Yii::$app->session->setFlash(
                'success',
                Yii::t('app', 'Successful saving')
            );
            return $this->refresh();
        }

        return $this->render('schedule', [
            'model' => $form,
            'staff' => $model
        ]);
    }

    /**
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Staff::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteImage($id)
    {
        $model = $this->findModel($id);

        $model->image_id = null;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful deleted'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDisableCalendar($id)
    {
        $model = $this->findModel($id);
        if (isset($model->user)) {
            $model->user->updateAttributes(['google_refresh_token' => null]);
        }
        return $this->redirect(['view', 'id' => $id]);
    }
}

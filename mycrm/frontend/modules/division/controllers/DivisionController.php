<?php

namespace frontend\modules\division\controllers;

use common\components\Model;
use core\forms\division\DivisionCreateForm;
use core\forms\division\DivisionUpdateForm;
use core\models\division\Division;
use core\models\division\DivisionImage;
use core\models\division\DivisionReview;
use core\models\division\DivisionSchedule;
use core\models\division\DivisionService;
use core\models\Image;
use core\models\ServiceCategory;
use core\models\Staff;
use core\services\division\DivisionModelService;
use core\services\division\dto\DivisionSettingsData;
use frontend\modules\division\search\DivisionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * DivisionController implements the CRUD actions for Division model.
 */
class DivisionController extends Controller
{
    private $divisionService;

    /**
     * DivisionController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param DivisionModelService $divisionService
     * @param array $config
     */
    public function __construct($id, $module, DivisionModelService $divisionService, $config = [])
    {
        $this->divisionService = $divisionService;
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
                'only' => ['index', 'update', 'service', 'create', 'delete', 'service', 'run', 'schedule'],
                'rules' => [
                    [
                        'actions' => ['index', 'update', 'service', 'create', 'delete', 'service', 'run', 'schedule'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    // 'service' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionSchedule($id)
    {
        $division = $this->findModel($id);
        $models   = $division->schedules;

        $hours = DivisionSchedule::defaultHours();
        $weekdays = DivisionSchedule::weekdays();

        if (empty($models)) {
            for ($weekday = 1; $weekday <= 7; $weekday++) {
                $newModel = new DivisionSchedule();
                $newModel->day_num = $weekday;
                $models[] = $newModel;
            }
        }

        if (Yii::$app->request->isPost) {
            $models = [];
            $schedules = Yii::$app->request->post('DivisionSchedule');
            foreach ($schedules as $key => $scheduleData) {
                $model = new DivisionSchedule();
                $model->attributes = $scheduleData;
                $model->division_id = $division->id;
                $models[] = $model;
            }

            // validate all models
            $valid = Model::validateMultiple($models) && DivisionSchedule::validateOverlapping($models);
            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                DivisionSchedule::deleteAll(['division_id' => $division->id]);
                foreach ($models as $key => $model) {
                    if (!($flag = $model->save(false))) {
                        $transaction->rollback();
                        break;
                    }
                }

                if ($flag) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                    return $this->redirect(['schedule', 'id' => $division->id]);
                }
            }
        }

        return $this->render('schedule', [
            'division' => $division,
            'models' => $models,
            'hours' => $hours,
            'weekdays' => $weekdays
        ]);
    }

    /**
     * Lists all Division models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new DivisionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Division model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $form = new DivisionCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $imageFile = UploadedFile::getInstance($form, 'image_file');
            if ($imageFile !== null
                && $image = Image::uploadImage($imageFile)
            ) {
                $form->logo_id = $image->id;
            }

            try {
                $model = $this->divisionService->create(
                    $form->getDto(),
                    $form->payments,
                    $form->phones,
                    new DivisionSettingsData(
                        $form->notification_time_before_lunch,
                        $form->notification_time_after_lunch
                    )
                );
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );

                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Division model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form  = new DivisionUpdateForm($model);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $imageFile = UploadedFile::getInstance($form, 'image_file');
            if ($imageFile !== null
                && $image = Image::uploadImage($imageFile)
            ) {
                $form->logo_id = $image->id;
            }

            try {
                $this->divisionService->edit(
                    $model->id,
                    $form->getDto(),
                    $form->payments,
                    $form->phones,
                    new DivisionSettingsData(
                        $form->notification_time_before_lunch,
                        $form->notification_time_after_lunch
                    )
                );
                Yii::$app->session->setFlash('success',
                    Yii::t('app', 'Successful saving'));

                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $form
        ]);
    }

    /**
     * Upload images
     *
     * @param $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionGallery($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            foreach ($imageFiles as $file) {
                $model->upload($file);
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
            return Json::encode($imageFiles);
        }

        return $this->render("gallery", ["model" => $model]);
    }

    /**
     * Removes division image
     *
     * @param string  $id
     * @param integer $image
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionImageRemove($id, $image)
    {
        $model          = $this->findModel($id);
        $division_image = DivisionImage::find()->where(['id' => $image, 'division_id' => $model->id])->one();
        $division_image->delete();
        $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Deletes an existing Division model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model         = $this->findModel($id);
        $model->disable();
        if ($model->update(true, ['status'])) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful deleted'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error deleting'));
        }
        return $this->redirect(['/company/default/update', 'id' => $model->company_id]);
    }

    /**
     * Lists divisions filtering
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionService()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Ajax staffs list
        $division_ids = Yii::$app->request->getBodyParam('division_id', null);
        $staff_id    = Yii::$app->request->getBodyParam('staff_id', 0);
        if (empty($division_ids)) {
            throw new NotFoundHttpException('Error page');
        }

        $data              = [];
        $categoryNames     = ArrayHelper::map(
            ServiceCategory::find()->all(),
            'id',
            'name'
        );
        $division_services = DivisionService::find()
            ->joinWith(['categories'], false)
            ->select([
                '{{%division_services}}.id as key',
                'service_name as title',
                '{{%service_categories}}.id as cat_id'
            ])
            ->division($division_ids, false)
            ->deleted(false)
            ->distinct()
            ->asArray()
            ->all();

        $division_services = ArrayHelper::index(
            $division_services,
            null,
            'cat_id'
        );

        $staff_services = [];
        if (!empty($staff_id)) {
            $staff = Staff::findOne($staff_id);
            $staff_services = ArrayHelper::getColumn($staff->divisionServices, 'id');
        }

        foreach ($division_services as $key => $services) {

            // if staff is identified iterate through services to set the "selected" attribute
            if ($staff_id !== 0) {
                foreach ($services as $k => $service) {
                    $selected = false;
                    if ($staff_id !== 0 && in_array($service['key'], $staff_services)) {
                        $selected = true;
                    }
                    $services[$k]['selected'] = $selected;
                }
            }

            // if service has category set associated attributes
            if ($key && isset($categoryNames[$key])) {
                $data[] = [
                    'title'    => '<b>' . $categoryNames[$key] . '</b>',
                    'children' => $services,
                    "expanded" => true,
                    'folder'   => true,
                    'selected' => false
                ];
            } else {
                $data[] = [
                    'title'    => '<b>Без категории</b>',
                    'children' => $services,
                    "expanded" => true,
                    'folder'   => true,
                    'selected' => false
                ];
            }

        }

        return [
            [
                "title"    => Yii::t('app', 'All services'),
                "selected" => false,
                "folder"   => true,
                "expanded" => true,
                "children" => $data,
            ]
        ];
    }

    /**
     * Update all division rating
     */
    public function actionRun()
    {
        /* @var Division[] $divisions */
        $divisions = Division::find()->all();
        foreach ($divisions as $division) {
            DivisionReview::updateDivisionRating($division);
        }

        echo "done";
    }

    /**
     * Finds the Division model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Division the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Division::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

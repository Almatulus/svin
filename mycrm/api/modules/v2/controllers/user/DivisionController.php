<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\user\DivisionSearch;
use core\forms\division\DivisionCreateForm;
use core\forms\division\DivisionUpdateForm;
use core\models\division\Division;
use core\models\Image;
use core\services\division\DivisionModelService;
use core\services\division\dto\DivisionData;
use core\services\division\dto\DivisionSettingsData;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class DivisionController extends BaseController
{
    public $modelClass = 'core\models\division\Division';
    private $divisions;

    /**
     * DivisionController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param DivisionModelService $divisions
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        DivisionModelService $divisions,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->divisions = $divisions;
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
                    'actions' => ['index', 'create', 'update', 'view', 'options'],
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
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function prepareDataProvider(): ActiveDataProvider
    {
        $searchModel = new DivisionSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string $action
     * @param Division $model
     * @param array $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update'])) {
            $permittedDivisionIds = \Yii::$app->user->identity->getPermittedDivisions();
            if (!in_array($model->id, $permittedDivisionIds)) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @return DivisionCreateForm|Division
     */
    public function actionCreate()
    {
        $form = new DivisionCreateForm();
        $form->attributes = Yii::$app->request->bodyParams;

        if ($form->validate()) {

            $imageFile = UploadedFile::getInstance($form, 'image_file');
            if ($imageFile !== null && $image = Image::uploadImage($imageFile)) {
                $form->logo_id = $image->id;
            }

            $divisionData = new DivisionData(
                $form->address,
                $form->category_id,
                $form->company_id,
                $form->city_id,
                $form->description,
                $form->latitude,
                $form->longitude,
                $form->name,
                $form->status,
                $form->url,
                $form->working_finish,
                $form->working_start,
                $form->default_notification_time,
                $form->logo_id
            );

            return $this->divisions->create($divisionData, $form->payments, $form->phones, new DivisionSettingsData(
                $form->notification_time_before_lunch,
                $form->notification_time_after_lunch
            ));
        }

        return $form;
    }

    /**
     * @param integer $id
     *
     * @return Division|DivisionUpdateForm
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        /* @var Division $model */
        $model = Division::find()->enabled()->andWhere(['id' => $id])->one();

        if ($model === null) {
            throw new NotFoundHttpException('Model does not exist.');
        }

        $this->checkAccess($this->action->id, $model);

        $form = new DivisionUpdateForm($model);
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->validate()) {
            return $this->divisions->edit(
                $model->id,
                $form->getDto(),
                $form->payments,
                $form->phones,
                new DivisionSettingsData(
                    $form->notification_time_before_lunch,
                    $form->notification_time_after_lunch
                )
            );
        }

        return $form;
    }
}

<?php

namespace api\modules\v2\controllers\staff;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\staff\ScheduleTemplateSearch;
use core\forms\staff\ScheduleTemplateForm;
use core\models\ScheduleTemplate;
use core\models\Staff;
use core\services\staff\dto\ScheduleTemplateData;
use core\services\staff\dto\TemplateIntervalData;
use core\services\staff\ScheduleTemplateService;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ScheduleTemplateController extends BaseController
{
    public $modelClass = 'core\models\ScheduleTemplate';

    /** @var ScheduleTemplateService */
    private $service;

    public function __construct($id, Module $module, ScheduleTemplateService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
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
                    'actions' => ['index', 'options', 'generate'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\web\BadRequestHttpException
     */
    public function prepareDataProvider()
    {
        $searchModel = new ScheduleTemplateSearch();
        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param int $staff_id
     * @return ScheduleTemplateForm|ScheduleTemplate
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGenerate(int $staff_id)
    {
        $staff = $this->findStaff($staff_id);

        $this->checkAccess($this->action->id, $staff);

        $form = new ScheduleTemplateForm();
        $form->setCompanyId(\Yii::$app->user->identity->company_id);

        if ($form->load(\Yii::$app->request->bodyParams) && $form->validate()) {
            $templateData = new ScheduleTemplateData(
                $staff_id,
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

            return $this->service->generate($templateData, $intervalData, new \DateTime($form->start));
        }

        return $form;
    }

    /**
     * @param int $staff_id
     * @return Staff
     * @throws NotFoundHttpException
     */
    private function findStaff(int $staff_id)
    {
        if ($model = Staff::findOne($staff_id)) {
            return $model;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param string $action
     * @param Staff $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['generate'])) {
            $permittedDivisionIds = \Yii::$app->user->identity->getPermittedDivisions();
            $staffDivisionIds = $model->getDivisions()->enabled()->select('id')->column();

            if (empty(array_intersect($staffDivisionIds, $permittedDivisionIds))) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }
}
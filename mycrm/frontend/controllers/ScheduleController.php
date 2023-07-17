<?php

namespace frontend\controllers;

use core\forms\ScheduleFilterForm;
use core\forms\StaffScheduleCreateForm;
use core\forms\StaffScheduleDeleteForm;
use core\forms\StaffScheduleUpdateForm;
use core\models\Staff;
use core\models\division\Division;
use core\services\StaffScheduleService;
use DateTime;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * StaffController implements the CRUD actions for Staff model.
 */
class ScheduleController extends Controller
{
    private $staffScheduleService;

    public function __construct(
        $id,
        $module,
        StaffScheduleService $staffScheduleService,
        $config = []
    ) {
        $this->staffScheduleService = $staffScheduleService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'add',
                            'delete',
                            'edit',
                            'index'
                        ],
                        'allow'   => true,
                        'roles'   => ['scheduleAdmin'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Show staff schedule
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $form = new ScheduleFilterForm();

        if ($form->load(Yii::$app->request->getQueryParams())
            && ! $form->validate()
        ) {
            throw new BadRequestHttpException(Yii::t('app', 'Wrong params'));
        }

        $staffs = Staff::find()
                        ->orderBy('{{%staffs}}.id')
                       ->valid()
                       ->company()
                       ->division($form->division_id)
                       ->permitted()
                       ->all();

        $divisions = Division::find()
            ->andFilterWhere(['id' => $form->division_id])
            ->company()
            ->permitted()
            ->enabled()
            ->all();
        
        if (empty($staffs)) {
            return $this->render('empty');
        }

        return $this->render('index', [
            'schedules' => Staff::getScheduleAt(
                new DateTime($form->start_date),
                new DateTime($form->end_date),
                $staffs
            ),
            'staffs'    => $staffs,
            'divisions' => $divisions,
            'model'     => $form,
        ]);
    }

    /**
     * Save schedule
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new StaffScheduleCreateForm();
        $form->load(Yii::$app->request->getQueryParams());

        if ( ! $form->validate()) {
            $error = $form->getErrors();
            throw new BadRequestHttpException(reset($error)[0]);
        }

        $this->staffScheduleService->add(
            $form->staff_id,
            $form->division_id,
            $form->date,
            $form->break_start,
            $form->break_end
        );

        Staff::invalidateDateSchedule(
            $form->division_id,
            new DateTime($form->date)
        );

        return ['error' => 200, 'message' => 'success', 'has_schedule' => true];
    }

    /**
     * Save schedule
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new StaffScheduleUpdateForm();
        $form->load(Yii::$app->request->getQueryParams());

        if ( ! $form->validate()) {
            $error = $form->getErrors();
            throw new BadRequestHttpException(reset($error)[0]);
        }

        $this->staffScheduleService->edit(
            $form->staff_id,
            $form->division_id,
            $form->date,
            $form->start,
            $form->end,
            $form->break_start,
            $form->break_end
        );

        Staff::invalidateDateSchedule(
            $form->division_id,
            new DateTime($form->date)
        );

        return [
            'error'        => 200,
            'message'      => 'success',
            'has_schedule' => true
        ];
    }

    /**
     * Delete schedule
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new StaffScheduleDeleteForm();
        $form->load(Yii::$app->request->getQueryParams());

        if ( ! $form->validate()) {
            $error = $form->getErrors();
            throw new BadRequestHttpException(reset($error)[0]);
        }

        $this->staffScheduleService->delete(
            $form->staff_id,
            $form->division_id,
            $form->date
        );

        Staff::invalidateDateSchedule(
            $form->division_id,
            new DateTime($form->date)
        );

        return [
            'error'        => 200,
            'message'      => 'success',
            'has_schedule' => false
        ];
    }
}

<?php

namespace api\modules\v2\controllers\document;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\document\DocumentTemplateSearch;
use core\forms\document\TemplateForm;
use core\models\document\DocumentTemplate;
use core\repositories\exceptions\NotFoundException;
use core\services\document\TemplateService;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class TemplateController extends BaseController
{
    public $modelClass = 'core\models\document\DocumentTemplate';

    /** @var TemplateService */
    private $service;

    /**
     * TemplateController constructor.
     * @param string $id
     * @param Module $module
     * @param TemplateService $service
     * @param array $config
     */
    public function __construct($id, Module $module, TemplateService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->service = $service;
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider'
        ];

        return $actions;
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
                    'actions' => [
                        'index',
                        'view',
                        'create',
                        'update',
                        'delete',
                        'options'
                    ],
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
    public function prepareDataProvider()
    {
        $searchModel = new DocumentTemplateSearch();
        $searchModel->created_user_id = \Yii::$app->user->id;

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param $id
     * @return TemplateForm|\core\models\document\DocumentTemplate
     * @throws NotFoundHttpException
     */
    public function actionCreate($id)
    {
        try {
            $form = new TemplateForm($id);
            $form->attributes = \Yii::$app->request->bodyParams;
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if ($form->validate()) {
            return $this->service->create($form, \Yii::$app->user->id);
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return TemplateForm|DocumentTemplate
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new TemplateForm($model->document_form_id);
        $form->attributes = \Yii::$app->request->bodyParams;

        if ($form->validate()) {
            return $this->service->update($id, $form);
        }

        return $form;
    }

    /**
     * @param $id
     * @return DocumentTemplate
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = DocumentTemplate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }
    }

    /**
     * @param string $action
     * @param DocumentTemplate $model
     * @param array $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update', 'delete'])) {
            if ($model->creator->company_id != \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

}
<?php

namespace api\modules\v2\controllers\document;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\document\DocumentSearch;
use core\forms\document\DocumentCreateForm;
use core\forms\document\DocumentUpdateForm;
use core\models\document\Document;
use core\repositories\exceptions\NotFoundException;
use core\services\document\DocumentService;
use yii\base\Module;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\document\Document';

    private $_service;

    /**
     * DefaultController constructor.
     * @param string $id
     * @param Module $module
     * @param DocumentService $service
     * @param array $config
     */
    public function __construct($id, Module $module, DocumentService $service, array $config = [])
    {
        $this->_service = $service;
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider'
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new DocumentSearch();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param string          $action
     * @param Document $model
     * @param array           $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update'])) {
            if ($model->companyCustomer->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @param $id - Document Form ID
     *
     * @return DocumentCreateForm|Document
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate($id)
    {
        try {
            $form = new DocumentCreateForm($id);
            $form->attributes = \Yii::$app->request->bodyParams;
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if ($form->validate()) {
            return $this->_service->create($form);
        }

        return $form;
    }

    /**
     * ToDo add permission to update own document
     *
     * @param $id - Document ID
     *
     * @return DocumentCreateForm|\core\models\document\Document
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $document = $this->findModel($id);

        $form = new DocumentUpdateForm($document->id);
        $form->attributes = \Yii::$app->request->bodyParams;

        if ($form->validate()) {
            return $this->_service->update($document, $form);
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return void
     * @throws NotFoundHttpException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGenerate($id)
    {
        $document = $this->findModel($id);

        $this->_service->generate($document);
    }

    /**
     * @param $id
     * @return Document
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Document::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }
    }
}
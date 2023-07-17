<?php

namespace frontend\modules\order\controllers;

use core\forms\order\OrderDocumentForm;
use core\models\order\OrderDocument;
use core\models\order\OrderDocumentTemplate;
use core\services\order\OrderDocumentService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class DocumentController extends Controller
{
    private $orderDocumentService;

    /**
     * DocumentController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param OrderDocumentService $orderDocumentService
     * @param array $config
     */
    public function __construct($id, $module, OrderDocumentService $orderDocumentService, $config = [])
    {
        $this->orderDocumentService = $orderDocumentService;
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
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $division_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionTemplates($division_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $division = \core\models\division\Division::findOne($division_id);
        $templates = OrderDocumentTemplate::find()
            ->where([
                'category_id' => $division->category_id
            ])
            ->andWhere([
                'OR',
                ['{{%order_document_templates}}.company_id' => $division->company_id],
                ['{{%order_document_templates}}.company_id' => null]
            ])
            ->select(["id", "name"])
            ->orderBy('name')
            ->asArray()
            ->all();
        return $templates;
    }

    /**
     * @return array|OrderDocument|string
     * @throws \Exception
     */
    public function actionGenerate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $form = new OrderDocumentForm();
        if ($form->load(Yii::$app->request->get()) && $form->validate()) {
            try {
                $document = $this->orderDocumentService->add($form->order_id, $form->template_id, Yii::$app->user->id);
                return $document;
            } catch (\DomainException $e) {
                return $e->getMessage();
            }
        }
        return $form->errors;
    }

    /**
     * @param $id
     * @return $this
     */
    public function actionGet($id)
    {
        $orderDocument = OrderDocument::findOne($id);
        return Yii::$app->response->sendFile(Yii::$app->basePath . $orderDocument->path);
    }
}
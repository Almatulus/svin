<?php

namespace frontend\modules\webcall\controllers;

use core\forms\webcall\AccountForm;
use core\models\webcall\WebcallAccount;
use core\services\company\WebCallService;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AccountController extends Controller
{
    private $service;

    public function __construct($id, $module, WebCallService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['create', 'delete'],
                        'allow'   => true,
                        'roles'   => ['webcallAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        if (!\Yii::$app->user->identity->company->hasWebCallAccess()) {
            \Yii::$app->session->setFlash('error', 'У компании не настроены звонки.');
        }

        $form = new AccountForm();

        if ($form->load(\Yii::$app->request->post()) && $form->validate()) {
            $this->service->createAccount(
                \Yii::$app->user->identity->company->webcall,
                $form
            );
            \Yii::$app->session->setFlash('success', 'Успешно создано');
            return $this->redirect(['default/settings']);
        }

        return $this->render('create', [
            'model' => $form
        ]);
    }

    /**
     * @param $id
     */
    public function actionDelete($id)
    {
        if (!\Yii::$app->user->identity->company->hasWebCallAccess()) {
            \Yii::$app->session->setFlash('error', 'У компании не настроены звонки.');
        }

        $model = $this->findModel($id);

        $this->service->deleteAccount(
            \Yii::$app->user->identity->company->webcall->id,
            $id
        );

        \Yii::$app->session->setFlash('success', \Yii::t('app', 'Successful deleted'));

        return $this->redirect(['default/settings']);
    }

    /**
     * Returns model
     * @param $id
     * @return WebcallAccount
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = WebcallAccount::find()->company()->andWhere(['{{%company_webcall_accounts}}.id' => $id])->one();
        if ($model == null) {
            throw new NotFoundHttpException('No web call is set up');
        }
        return $model;
    }
}
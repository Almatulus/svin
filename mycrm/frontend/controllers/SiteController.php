<?php

namespace frontend\controllers;

use core\forms\HelpForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

class SiteController extends \yii\web\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['faq', 'support', 'error'],
                'rules' => [
                    [
                        'actions' => ['faq', 'support', 'error'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->getView()->params['bodyClass']    = 'no_sidenav';
        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionSupport()
    {
        $model = new HelpForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->attachment = UploadedFile::getInstance($model, 'attachment');

            $companyName  = Yii::$app->user->identity->company->name;
            $companyPhone = Yii::$app->user->identity->username;

            $messageSubject = Yii::t('app', 'Request from {company_name}',
                ['company_name' => $companyName]);

            $message = Yii::$app->mailer->compose("layouts/help_request", [
                'companyName' => $companyName,
                'companyPhone' => $companyPhone,
                'text' => $model->query
            ])
                ->setSubject($messageSubject)
                ->setFrom(Yii::$app->params['supportEmail'])
                ->setTo(Yii::$app->params['supportEmail']);

            if ($model->attachment) {
                $model->saveAttachment();
                $message->attach($model->filename);
            }

            if ($message->send()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Your query has been sent. We will reply as soon as possible.'));
            }

            if ($model->attachment) {
                $model->deleteAttachment();
            }
        }

        return $this->render('support', compact('model'));
    }

    /**
     * @return string
     */
    public function actionFaq()
    {
        $models = \core\models\FaqItem::find()->orderBy("id ASC")->all();
        return $this->render("faq", ["models" => $models]);
    }
}

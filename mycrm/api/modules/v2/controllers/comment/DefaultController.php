<?php

namespace api\modules\v2\controllers\comment;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\comment\MedCardCommentSearch;
use api\modules\v2\search\company\MedCardCompanyCommentSearch;
use Yii;
use yii\filters\AccessControl;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\medCard\MedCardComment';

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
                        'options',
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
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        $searchModel = new MedCardCommentSearch(); // MedCartComment
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $commonComments = $dataProvider->getModels();

        $searchModel = new MedCardCompanyCommentSearch(); // MedCartCompanyComment
        $searchModel->company_id = Yii::$app->user->identity->company_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $companyComments = $dataProvider->getModels();

        return array_merge($commonComments, $companyComments);
    }
}

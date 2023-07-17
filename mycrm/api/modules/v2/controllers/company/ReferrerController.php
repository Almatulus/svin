<?php

namespace api\modules\v2\controllers\company;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\company\ReferrerSearch;
use core\models\company\Referrer;
use api\modules\v2\OptionsTrait;
use Yii;
use yii\web\BadRequestHttpException;

class ReferrerController extends BaseController
{
    use OptionsTrait;

    public $modelClass = 'core\models\company\Referrer';

    public function beforeAction($event)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($event);
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
     * @return Referrer
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new Referrer();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if (!($model->validate() && $model->save())) {
            throw new \InvalidArgumentException('Failed to create the object');
        }

        Yii::$app->getResponse()->setStatusCode(201);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new ReferrerSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }
}

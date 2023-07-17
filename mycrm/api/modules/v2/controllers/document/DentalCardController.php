<?php

namespace api\modules\v2\controllers\document;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\document\DentalCardElementSearch;
use api\modules\v2\search\document\DocumentSearch;
use core\forms\document\DocumentCreateForm;
use core\forms\document\DocumentUpdateForm;
use core\models\document\Document;
use core\repositories\exceptions\NotFoundException;
use core\services\document\DocumentService;
use yii\base\Module;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DentalCardController extends BaseController
{
    public $modelClass = 'core\models\document\DentalCardElement';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['view']);
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
        $searchModel = new DentalCardElementSearch();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }
}
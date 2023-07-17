<?php

namespace api\modules\v2\controllers\staff;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\document\DocumentFormSearch;
use core\models\Staff;
use yii\web\NotFoundHttpException;

class DocumentFormController extends BaseController
{
    public $modelClass = 'core\models\document\DocumentForm';

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
     * Get DocumentForms related to the given Staff and his CompanyPositions.
     * Similar to @see FormController::prepareDataProvider() except for Staff ID.
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new DocumentFormSearch();

        $staff_id = \Yii::$app->request->queryParams['staff_id'];
        $staff = $this->findStaff($staff_id);
        $searchModel->companyPositionIDs = $staff->getCompanyPositions()->select(['id'])->column();

        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param int $staff_id
     * @return Staff
     * @throws NotFoundHttpException
     */
    private function findStaff(int $staff_id)
    {
        if ($model = Staff::findOne($staff_id)) {
            $permittedDivisionIds = \Yii::$app->user->identity->getPermittedDivisions();
            $staffDivisionIds = $model->getDivisions()->enabled()->select('id')->column();

            if (!empty(array_intersect($staffDivisionIds, $permittedDivisionIds))) {
                return $model;
            }
        }

        throw new NotFoundHttpException();
    }

}
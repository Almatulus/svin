<?php

namespace api\modules\v2\controllers\order;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\OptionsTrait;
use core\forms\order\file\UploadFileForm;
use core\services\order\OrderStorageService;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

class FileController extends BaseController
{
    public $modelClass = 'core\models\order\Order';
    private $orderStorageService;

    public function __construct(
        $id,
        $module,
        OrderStorageService $orderStorageService,
        $config = []
    ) {
        $this->orderStorageService = $orderStorageService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['index']);

        return $actions;
    }

    /**
     * @param integer $order_id
     *
     * @return UploadFileForm|\core\models\File
     * @throws ForbiddenHttpException
     * @throws \Exception
     */
    public function actionUpload($order_id)
    {
        if ( ! Yii::$app->user->identity->company->canUploadFiles()) {
            throw new ForbiddenHttpException(
                Yii::t('yii', 'You are not allowed to perform this action.')
            );
        }

        $form = new UploadFileForm();
        $form->order_id = $order_id;
        $form->load(Yii::$app->request->bodyParams, '');
        $form->file = UploadedFile::getInstanceByName('file');
        if ($form->validate()) {
            return $this->orderStorageService->upload(
                Yii::$app->user->identity->company_id,
                $form->order_id,
                $form->file->name,
                $form->file->tempName
            );
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        if ( ! Yii::$app->user->identity->company->canUploadFiles()) {
            throw new ForbiddenHttpException(
                Yii::t('yii', 'You are not allowed to perform this action.')
            );
        }

        try {
            $this->orderStorageService->delete($id);
        } catch (\DomainException $e) {
            return [
                'error'   => $e->getMessage(),
                'message' => 'Произошла ошибка при удалении файла',
            ];
        }

        return ['message' => Yii::t('app', 'Successful deleted')];
    }
}

<?php

namespace api\modules\v1\controllers;

use core\models\customer\Customer;
use core\models\Image;
use api\modules\v1\components\ApiController;
use Exception;
use Yii;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class CustomerController extends ApiController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                'tokenParam' => 'token',
            ]
        ]);
    }

    public function actionIndex()
    {
        $model = \Yii::$app->user->identity;
        /* @var Customer $model */
        return $model->getInformation();
    }

    public function actionUpdate()
    {
        $name = \Yii::$app->request->getBodyParam('name', null);
        $lastname = \Yii::$app->request->getBodyParam('lastname', null);
        $email = \Yii::$app->request->getBodyParam('email', null);
        $photo = UploadedFile::getInstanceByName('photo');

        $model = \Yii::$app->user->identity;
        /* @var Customer $model */
        if ($name !== null) {
            $model->name = $name;
        }
        if ($lastname !== null) {
            $model->lastname = $lastname;
        }
        if ($email !== null) {
            $model->email = $email;
        }
        if ($photo !== null) {
            if (($image = Image::uploadImage($photo)) !== null) {
                $model->image_id = $image->id;
            }
        }

        if ($model->save()) {
            return ['status' => 200, 'message' => 'Success', 'customer' => $model->getInformation()];
        }

        return ['status' => 500, 'message' => 'Error while creating'];
    }

    public function actionPassword()
    {
        $password = Yii::$app->request->getBodyParam('password', null);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            /* @var Customer $customer */
            $customer = \Yii::$app->user->identity;
            $customer->password_hash = $password;
            if (!$customer->save()) {
                throw new Exception('Error while customer saving');
            }

            $transaction->commit();
            return ['status' => 200, 'message' => 'Password successfully saved'];
        } catch (Exception $e) {
            $transaction->rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function actionKey()
    {
        $key = \Yii::$app->request->getBodyParam('key', null);
        $type = \Yii::$app->request->getBodyParam('type', null);

        if ($key !== null && $type !== null) {
            if (\Yii::$app->user->identity->setKey($type, $key)) {
                return ['status' => 200, 'message' => 'Success'];
            }

            return ['status' => 500, 'message' => 'Error while '];
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

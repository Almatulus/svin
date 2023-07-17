<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use common\components\Model;
use core\models\customer\CustomerRequestTemplate;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class SmsTemplateController extends BaseController
{
    public $modelClass = 'core\models\customer\CustomerRequestTemplate';

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
                    'actions' => ['index', 'update', 'options'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @return CustomerRequestTemplate[]
     */
    public function actionIndex()
    {
        return CustomerRequestTemplate::loadTemplates();
    }

    /**
     * @return CustomerRequestTemplate[]
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionUpdate()
    {
        /* @var $templates CustomerRequestTemplate[] */
        $templates = CustomerRequestTemplate::loadTemplates();
        $templates = ArrayHelper::index($templates, 'key');

        $params = Yii::$app->request->bodyParams;

        if ( ! ($params && Model::loadMultiple($templates, $params, ''))) {
            throw new BadRequestHttpException('Wrong request params');
        }

        if (!Model::validateMultiple($templates)) {
            Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
            return array_map(function (CustomerRequestTemplate $template) {
                return $template->errors;
            }, $templates);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($templates as $template) {
                if ( ! $template->save()) {
                    $errors = $template->getErrors();
                    throw new \DomainException(reset($errors)[0]);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return array_values($templates);
    }
}

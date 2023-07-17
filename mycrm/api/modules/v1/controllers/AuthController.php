<?php

namespace api\modules\v1\controllers;

use core\helpers\Security;
use core\models\ApiHistory;
use core\models\customer\Customer;
use core\models\customer\CustomerRequest;
use api\modules\v1\components\ApiController;
use Yii;
use yii\db\Exception;
use yii\web\HttpException;

class AuthController extends ApiController
{
    /**
     * Validate access token
     * @return mixed
     * @throws HttpException
     */
    public function actionIndex()
    {
        $number = Yii::$app->request->getBodyParam('number', null);
        $code = Yii::$app->request->getBodyParam('code', null);
        if ($number === null || $code === null) {
            throw new HttpException(400);
        }

        $number = preg_replace('/\s+/', '', $number);
        if (preg_match('/^\+(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $number, $m)) {
            $number = "+{$m[1]} {$m[2]} {$m[3]} {$m[4]} {$m[5]}";
        }

        $request = CustomerRequest::getCustomerRequest($number, $code);
        if ($request) {
            $access_token = $request->customer->regenerateAccessToken();
            if ($access_token !== null) {
                return ['status' => 200, 'message' => $access_token];
            } else {
                return ['status' => 500, 'message' => 'Changing access token error'];
            }
        } else {
            return ['status' => 403, 'message' => 'Customer with given code not found'];
        }
    }

    /**
     * Receive request to send sms
     * @throws Exception
     * @throws HttpException
     */
    public function actionRegister()
    {
        $number = Yii::$app->request->getBodyParam('number', null);
        if ($number === null) {
            throw new HttpException(400);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $number = preg_replace('/\s+/', '', $number);
            if (preg_match('/^\+(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $number, $m)) {
                $number = "+{$m[1]} {$m[2]} {$m[3]} {$m[4]} {$m[5]}";
            }

            $customer = Customer::findOne(['phone' => $number]);
            if ($customer == null) {
                $customer = new Customer();
                $customer->phone = $number;
                if (!$customer->save()) {
                    // var_dump($customer->getErrors());
                    throw new Exception(json_encode($customer->getErrors()));
                }
            }

            $request = new CustomerRequest();
            $request->type = CustomerRequest::TYPE_REGISTER;
            $request->status = CustomerRequest::STATUS_ENABLED;
            $request->customer_id = $customer->id;
            $request->code = Security::random_str(CustomerRequest::CODE_SIZE);
            $request->receiver_phone = $customer->phone;
            if ($request->save()) {
                $transaction->commit();
                $request->sendSMS();
                return ['status' => 200, 'message' => 'Sms to your number is sent'];
            } else {
                throw new Exception('Request saving error');
            }

        } catch (Exception $e) {
            $transaction->rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send code for password changing
     * @throws HttpException
     */
    public function actionPassword()
    {
        $number = Yii::$app->request->getBodyParam('number', null);
        $code = Yii::$app->request->getBodyParam('code', null);
        $password = Yii::$app->request->getBodyParam('password', null);
        if ($number === null || $code === null || $password === null) {
            throw new HttpException(400);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = CustomerRequest::getCustomerRequest($number, $code);
            if (!$request) {
                throw new Exception('Customer with given code not found');
            }

            $customer = $request->customer;
            $customer->password_hash = $password;
            if (!$customer->save()) {
                throw new Exception('Error while customer saving');
            }

            $access_token = $customer->regenerateAccessToken();
            if ($access_token == null) {
                throw new Exception('Changing access token error');
            }

            if (!$request->setUsed()) {
                throw new Exception('Error while setting token used');
            }

            $transaction->commit();
            return ['status' => 200, 'message' => $access_token];
        } catch (Exception $e) {
            $transaction->rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    /**
     * Register through phone/password
     * @throws HttpException
     */
    public function actionLogin()
    {
        $number = Yii::$app->request->getBodyParam('number', null);
        $password = Yii::$app->request->getBodyParam('password', null);
        if ($number === null || $password === null) {
            throw new HttpException(400);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var $customer Customer|null */
            $customer = Customer::findOne(['phone' => $number]);
//            if ($customer == null)
//            {
//                $customer = new Customer();
//                $customer->phone = $number;
////                $customer->password_hash = $password;
//                if(!$customer->save())
//                {
//                    throw new Exception('Customer not saved' . Json::encode($customer->getErrors()));
//                }
//            }

            if ($customer && $customer->validatePassword($password)) {
                $access_token = $customer->regenerateAccessToken();
                if ($access_token !== null) {
                    $transaction->commit();
                    return ['status' => 200, 'message' => $access_token, 'name' => $customer->getFullName()];
                } else {
                    return ['status' => 500, 'message' => 'Changing access token error'];
                }
            } else {
                return ['status' => 403, 'message' => 'Password incorrect'];
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return ['status' => 500, 'message' => 'Register error'];
        }

    }
}

<?php

namespace core\services\company;

use core\forms\webcall\AccountForm;
use core\models\webcall\WebCall;
use core\models\webcall\WebcallAccount;
use core\repositories\WebcallRepository;
use core\services\TransactionManager;

class WebCallService
{
    private $transactionManager;
    private $webCalls;

    public function __construct(
        TransactionManager $transactionManager,
        WebcallRepository $webCalls
    ) {
        $this->transactionManager = $transactionManager;
        $this->webCalls = $webCalls;
    }

    /**
     * @param $id
     * @param $api_key
     * @param $username
     * @param $domain
     *
     * @throws \Exception
     */
    public function updateSettings($id, $api_key, $username, $domain)
    {
        $model = $this->webCalls->find($id);

        $model->edit($api_key, $username, $domain);

        $this->transactionManager->execute(function() use ($model){
            $this->webCalls->save($model);
        });
    }

    /**
     * @param WebCall $webCall
     * @param AccountForm $form
     */
    public function createAccount(WebCall $webCall, AccountForm $form)
    {
        $account = WebcallAccount::add($form->division_id, $form->name, $form->email);

        $this->transactionManager->execute(function () use ($account, $webCall, $form) {
            $this->webCalls->save($account);
            $webCall->createEmployer($account->name, $account->email, $form->password);
        });
    }

    /**
     * @param int $web_call_id
     * @param int $account_id
     */
    public function deleteAccount(int $web_call_id, int $account_id)
    {
        $webCall = $this->webCalls->find($web_call_id);
        $account = $this->webCalls->findAccount($account_id);

        $this->transactionManager->execute(function () use ($account, $webCall) {
            $this->webCalls->delete($account);
            $webCall->deleteEmployer($account->email);
        });
    }

}
<?php

namespace core\forms\customer;


class SmscCallbackForm extends \yii\base\Model {

    public $id;
    public $phone;
    public $status;
    public $time;
    public $err;
    public $cnt;
    public $cost;
    public $flag;

    /**
     * @return array
     */
    public function rules() {
        return [
            [['id'], 'required'],
            [['phone', 'status', 'time', 'err', 'cnt', 'cost', 'flag'], 'safe'],
        ];
    }

    public function formName()
    {
        return '';
    }
}
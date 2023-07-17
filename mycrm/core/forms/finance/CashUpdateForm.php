<?php

namespace core\forms\finance;

use core\models\finance\CompanyCash;
use Yii;
use yii\base\Model;

class CashUpdateForm extends Model
{
    public $init_money;
    public $comments;
    public $name;
    public $cash;

    /**
     * CashUpdateForm constructor.
     * @param CompanyCash $cash
     * @param array $config
     */
    public function __construct(CompanyCash $cash, $config = [])
    {
        $this->cash = $cash;
        $this->attributes = $cash->attributes;
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['init_money', 'default', 'value' => 0],
            ['init_money', 'integer', 'min' => 0],
            [['name'], 'required'],
            [['comments'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'       => Yii::t('app','Name'),
            'comments'   => Yii::t('app','Comments'),
            'init_money' => Yii::t('app', 'Init Money'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'CompanyCash';
    }
}

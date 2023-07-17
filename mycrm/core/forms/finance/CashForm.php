<?php

namespace core\forms\finance;

use core\models\finance\CompanyCash;
use Yii;
use yii\base\Model;

class CashForm extends Model
{
    public $comments;
    public $division_id;
    public $init_money;
    public $is_deletable;
    public $name;
    public $type;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'division_id'], 'required'],
            [['division_id', 'type', 'init_money', 'is_deletable'], 'integer'],
            [['comments'], 'string'],
            [['name'], 'string', 'max' => 255],
            ['init_money', 'default','value' => 0],
            ['is_deletable', 'default','value' => true],
            ['type', 'default', 'value' => CompanyCash::TYPE_CASH_BOX],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'name' => Yii::t('app','Name'),
            'division_id' => Yii::t('app','Division'),
            'type' => Yii::t('app','Contractor Type'),
            'init_money' => Yii::t('app','Init Money'),
            'comments' => Yii::t('app','Comments'),
            'is_deletable' => Yii::t('app', 'Is Deletable')
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

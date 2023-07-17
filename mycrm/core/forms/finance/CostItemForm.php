<?php

namespace core\forms\finance;

use core\models\division\Division;
use Yii;
use yii\base\Model;

class CostItemForm extends Model
{
    public $comments;
    public $company_id;
    public $divisions = [];
    public $name;
    public $type;
    public $category_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['company_id', 'default', 'value' => \Yii::$app->user->identity->company_id],
            [['name', 'company_id', 'divisions'], 'required'],
            [['type', 'company_id', 'category_id'], 'integer'],
            [['comments'], 'string'],
            [['name'], 'string', 'max' => 255],

            ['divisions', 'filter', 'filter' => function ($data) {
                return json_decode($data);
            }, 'skipOnArray' => true],
            ['divisions', 'each', 'rule' => ['integer']],
            ['divisions', 'each', 'rule' => ['exist', 'skipOnError' => false,
                'targetClass' => Division::className(), 'targetAttribute' => "id"]],
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
            'divisions' => Yii::t('app','Divisions'),
            'company_id' => Yii::t('app','Company'),
            'type' => Yii::t('app','Contractor Type'),
            'init_money' => Yii::t('app','Init Money'),
            'comments' => Yii::t('app','Comments'),
            'is_deletable' => Yii::t('app', 'Is Deletable'),
            'category_id' => Yii::t('app','Category'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'CostItem';
    }
}

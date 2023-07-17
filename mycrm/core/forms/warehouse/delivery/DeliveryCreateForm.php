<?php

namespace core\forms\warehouse\delivery;

use core\models\finance\CompanyContractor;
use Yii;
use yii\base\Model;


/*
* @property integer $contractor_id
* @property string $delivery_date
* @property integer $division_id
* @property string $invoice_number
* @property string $notes
* @property integer $type
*
 * @property Company $company
* @property CompanyContractor $contractor
* @property DeliveryProduct[] $products
*/
class DeliveryCreateForm extends Model
{
    public $contractor_id;
    public $delivery_date;
    public $invoice_number;
    public $division_id;
    public $notes;

    public function init()
    {
        if ( ! $this->delivery_date) {
            $this->delivery_date = date("Y-m-d");
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'delivery_date'], 'required'],
            [['contractor_id', 'division_id'], 'integer'],
            [['delivery_date', 'created_at', 'updated_at'], 'safe'],
            [['invoice_number', 'notes'], 'string', 'max' => 255],

            [['contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyContractor::className(), 'targetAttribute' => ['contractor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'contractor_id' => Yii::t('app', 'Contractor'),
            'delivery_date' => Yii::t('app', 'Delivery date'),
            'division_id' => Yii::t('app', 'Division ID'),
            'invoice_number' => Yii::t('app', 'Invoice number'),
            'notes' => Yii::t('app', 'Comments'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Delivery';
    }
}

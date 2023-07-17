<?php

namespace core\forms\warehouse;

use core\models\warehouse\SaleProduct;
use Yii;
use yii\base\Model;

class SaleAnalysisForm extends Model {

    public $start_date;
    public $end_date;
    public $category_id;
    public $staff_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_date', 'end_date'], 'safe'],
            [['category_id', 'staff_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->end_date = date("Y-m-d");
        $this->start_date = date("Y-m-d", strtotime($this->end_date . " -1 months"));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'start_date' => Yii::t('app', 'From date'),
            'end_date' => Yii::t('app', 'To date'),
            'category_id' => Yii::t('app', 'Category'),
            'staff_id' => Yii::t('app', 'Staff ID')
        ];
    }

    /**
     * @return $this
     */
    public function getProviderQuery()
    {
        $query = SaleProduct::find()
            ->joinWith(['sale', 'product.unit'], [true, true])
            ->andWhere(['{{%warehouse_sale}}.division_id' => Yii::$app->user->identity->permittedDivisions])
            ->andWhere(':start_date <= sale_date AND sale_date <= :end_date', [
                ':start_date' => $this->start_date,
                ':end_date' => $this->end_date
            ]);

        if ($this->category_id) {
            $query->andWhere(['category_id' => $this->category_id]);
        }
        if ($this->staff_id) {
            $query->andWhere(['staff_id' => $this->staff_id]);
        } else if ($this->staff_id == '0') {
            $query->andWhere('staff_id IS NULL');
        }

        return $query;
    }

}
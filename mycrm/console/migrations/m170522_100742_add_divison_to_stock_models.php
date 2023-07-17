<?php

use core\models\warehouse\Delivery;
use core\models\warehouse\Product;
use core\models\warehouse\Sale;
use core\models\warehouse\Stocktake;
use core\models\warehouse\Usage;
use yii\db\Migration;

class m170522_100742_add_divison_to_stock_models extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $deliveries = Delivery::find()->all();
        $products = Product::find()->all();
        $sales = Sale::find()->all();
        $stocktakes = Stocktake::find()->all();
        $usages = Usage::find()->all();

        $this->addColumn('{{%warehouse_delivery}}', 'division_id', $this->integer());
        $this->addColumn('{{%warehouse_product}}', 'division_id', $this->integer());
        $this->addColumn('{{%warehouse_sale}}', 'division_id', $this->integer());
        $this->addColumn('{{%warehouse_stocktake}}', 'division_id', $this->integer());
        $this->addColumn('{{%warehouse_usage}}', 'division_id', $this->integer());

        $this->addForeignKey('fk_delivery_division', '{{%warehouse_delivery}}', 'division_id', '{{%divisions}}', 'id');
        $this->addForeignKey('fk_product_division', '{{%warehouse_product}}', 'division_id', '{{%divisions}}', 'id');
        $this->addForeignKey('fk_sale_division', '{{%warehouse_sale}}', 'division_id', '{{%divisions}}', 'id');
        $this->addForeignKey('fk_stocktake_division', '{{%warehouse_stocktake}}', 'division_id', '{{%divisions}}', 'id');
        $this->addForeignKey('fk_usage_division', '{{%warehouse_usage}}', 'division_id', '{{%divisions}}', 'id');

        $this->setDivision($deliveries);
        $this->setDivision($products);
        $this->setDivision($sales);
        $this->setDivision($stocktakes);
        $this->setDivision($usages);

        // $this->execute('ALTER TABLE {{%warehouse_delivery}} ALTER COLUMN division_id SET NOT NULL');
        // $this->execute('ALTER TABLE {{%warehouse_product}} ALTER COLUMN division_id SET NOT NULL');
        // $this->execute('ALTER TABLE {{%warehouse_sale}} ALTER COLUMN division_id SET NOT NULL');
        // $this->execute('ALTER TABLE {{%warehouse_stocktake}} ALTER COLUMN division_id SET NOT NULL');
        // $this->execute('ALTER TABLE {{%warehouse_usage}} ALTER COLUMN division_id SET NOT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_delivery}}', 'division_id');
        $this->dropColumn('{{%warehouse_product}}', 'division_id');
        $this->dropColumn('{{%warehouse_sale}}', 'division_id');
        $this->dropColumn('{{%warehouse_stocktake}}', 'division_id');
        $this->dropColumn('{{%warehouse_usage}}', 'division_id');
    }

    private function setDivision($models)
    {
        foreach ($models as $key => $model) {
            $company = null;
            if (isset($model->company)) {
                $company = $model->company;
            } else {
                if ($model instanceof Product) {
                    if (isset($model->category->company)) {
                        $company = $model->company;
                    }
                } else if ($model instanceof Delivery) {
                    if (isset($model->contractor->company)) {
                        $company = $model->contractor->company;
                    }
                } else if ($model instanceof Sale) {
                    if (isset($model->cash->company)) {
                        $company = $model->cash->company;
                    } else if (isset($model->companyCustomer->company)) {
                        $company = $model->companyCustomer->company;
                    } else if (isset($model->staff->division->company)) {
                        $company = $model->staff->division->company;
                    }
                } else if ($model instanceof Stocktake) {
                    if (isset($model->creator->company)) {
                        $company = $model->creator->company;
                    }
                } else if ($model instanceof Usage) {
                    if (isset($model->companyCustomer->company)) {
                        $company = $model->companyCustomer->company;
                    } else if (isset($model->staff->division->company)) {
                        $company = $model->staff->division->company;
                    }
                }
            }

            if ($company) {
                $division = current($company->divisions);
                $model->division_id = $division->id;
                $model->update(false);
            }
        }
    }

}

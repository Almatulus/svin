<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m180410_075420_remove_static_service_categories
 */
class m180410_075420_remove_static_service_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $companies = Company::find()->innerJoinWith([
            'divisions' => function (\core\models\division\query\DivisionQuery $query) {
                return $query->category(2);
            }
        ])->orderBy('id ASC');

        foreach ($companies->each(100) as $company) {
            $division_ids = array_map(function (\core\models\division\Division $division) {
                return $division->id;
            }, $company->divisions);

            $serviceCategories = \core\models\ServiceCategory::find()->byDivisions($division_ids)->staticType()->orderBy('name ASC')->all();

            echo "Company = {$company->id}, {$company->name} ";
            echo sizeof($serviceCategories) > 0 ? ("has " . sizeof($serviceCategories) . " categories") : "doesn't have categories";
            echo PHP_EOL;

            foreach ($serviceCategories as $serviceCategory) {

                $divisionServices = $serviceCategory->getDivisionServices()->division($division_ids)->all();

                if (sizeof($divisionServices) > 0) {

                    echo "\t{$serviceCategory->name} has " . sizeof($divisionServices) . " division services. New Dynamic Category will be created" . PHP_EOL;

                    $dynamicCategory = new \core\models\ServiceCategory([
                        'parent_category_id' => 2,
                        'company_id'         => $company->id,
                        'name'               => ucfirst($serviceCategory->name),
                        'type'               => \core\models\ServiceCategory::TYPE_CATEGORY_DYNAMIC
                    ]);

                    if ($dynamicCategory->save(false)) {
                        foreach ($divisionServices as $divisionService) {
                            $divisionService->link('categories', $dynamicCategory);
                            $serviceCategory->unlink('divisionServices', $divisionService, true);
                        }
                    } else {
                        echo "{$company->id} {$serviceCategory->name}" . PHP_EOL;
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180410_075420_remove_static_service_categories cannot be reverted.\n";

        return false;
    }
    */
}

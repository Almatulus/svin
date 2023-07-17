<?php

use core\models\Service;
use core\models\ServiceCategory;
use yii\db\Migration;

/**
 * Class m180226_044658_refactor_services
 */
class m180226_044658_refactor_services extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%division_services_map}}', 'category_id', $this->integer()->unsigned());

        $this->addForeignKey('fk_division_service_map_category', '{{%division_services_map}}',
            'category_id', '{{%service_categories}}', 'id');

        $handledServices = [];

        $services = (new \yii\db\Query())
            ->select([
                's.id',
                's.category_id',
                'sc.company_id',
                'sc.type',
                'sc.parent_category_id',
                's.name as service_name',
                'sc.name as category_name'
            ])
            ->from('{{%services}} s')
            ->leftJoin('{{%service_categories}} sc', 'sc.id = s.category_id')
            ->orderBy('s.id ASC');

        foreach ($services->each(100) as $service) {

            if (isset($handledServices[$service['id']])) {
                continue;
            }

            $parent_category_id = $service['type'] == ServiceCategory::TYPE_CATEGORY_STATIC ? $service['category_id'] :
                $service['parent_category_id'];
            $name = $service['type'] == ServiceCategory::TYPE_CATEGORY_STATIC ? $service['service_name'] :
                $service['category_name'];

            $data = [
                'parent_category_id' => $parent_category_id,
                'name'               => $name,
                'company_id'         => $service['company_id'],
                'type'               => $service['type']
            ];

            $category = ServiceCategory::find()->where($data)->one();

            if (!$category) {
                $this->insert('{{%service_categories}}', $data);
                $categoryId = $this->db->lastInsertID;
            } else {
                $categoryId = $category->id;
            }

            $this->update('{{%division_services_map}}', ['category_id' => $categoryId],
                ['service_id' => $service['id']]);

            $handledServices[] = $service['id'];
        }

        $this->execute('ALTER TABLE {{%division_services_map}} ALTER COLUMN category_id SET NOT NULL');
        $this->dropColumn('{{%division_services_map}}', 'service_id');
        $this->dropTable('{{%services}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createTable('{{%services}}', [
            'id'          => $this->primaryKey(),
            'name'        => $this->string()->notNull(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'is_trial'    => $this->boolean()->defaultValue(false)
        ]);
        $this->addForeignKey('fk_services_category', '{{%services}}', 'category_id', '{{%service_categories}}', 'id');

        $this->addColumn('{{%division_services_map}}', 'service_id', $this->integer()->unsigned());

        $this->addForeignKey('fk_division_service_map_service', '{{%division_services_map}}', 'service_id',
            '{{%services}}', 'id');

        $categoriesQuery = ServiceCategory::find()->joinWith('parentCategory pc')->notRoot()->orderBy('id ASC');

        $this->execute('ALTER TABLE {{%division_services_map}} ALTER COLUMN category_id DROP NOT NULL');

        foreach ($categoriesQuery->each(100) as $category) {
            /** @var ServiceCategory $category */
            if ($category->isDynamic()
                || ($category->isStatic() && $category->parentCategory->parent_category_id != null)) {

                $this->insert('{{%services}}', [
                    'name'        => $category->name,
                    'category_id' => $category->isDynamic() ? $category->id : $category->parent_category_id
                ]);

                $this->update('{{%division_services_map}}', [
                    'category_id' => null,
                    'service_id'  => $this->db->lastInsertID
                ], ['category_id' => $category->id]);

                if ($category->isStatic()) {
                    $this->delete('{{%service_categories}}', ['id' => $category->id]);
                }
            }
        }

        $this->dropColumn('{{%division_services_map}}', 'category_id');

        $this->execute('ALTER TABLE {{%division_services_map}} ALTER COLUMN service_id SET NOT NULL');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180226_044658_refactor_services cannot be reverted.\n";

        return false;
    }
    */
}

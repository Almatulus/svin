<?php

namespace api\modules\v2\search\staff;

use core\models\division\DivisionService;
use core\models\division\query\DivisionServiceQuery;
use core\models\query\ServiceCategoryQuery;
use core\models\ServiceCategory;
use yii\base\Model;

class CategorySearch extends Model
{
    private $staff_id;

    public $division_id;

    /**
     * CategorySearch constructor.
     * @param int $staff_id
     * @param array $config
     */
    public function __construct(int $staff_id, array $config = [])
    {
        $this->staff_id = $staff_id;

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['division_id', 'integer'],
        ];
    }

    /**
     * @param $params
     * @return array
     */
    public function search($params)
    {
        $this->load($params, "");

        $query = ServiceCategory::find()
            ->distinct()
            ->innerJoinWith([
                "services" => function (DivisionServiceQuery $query) {
                    return $query->joinWith(['staffs', 'divisions'], false)
                        ->deleted(false)
                        ->andWhere([
                            'staff_id'    => $this->staff_id,
                            'division_id' => $this->division_id
                        ]);
                }
            ], false)
            ->with([
                'services' => function (DivisionServiceQuery $query) {
                    return $query->joinWith(['staffs', 'divisions'], false)
                        ->deleted(false)
                        ->andWhere([
                            'staff_id'    => $this->staff_id,
                            'division_id' => $this->division_id
                        ])
                        ->orderBy('service_name ASC');
                }
            ])
            ->orderBy('name ASC')
            ->asArray();

        $categories = $query->all();

        $servicesWithoutCategories = DivisionService::find()
            ->joinWith(['staffs', 'divisions'], false)
            ->joinWith([
                'categories' => function (ServiceCategoryQuery $query) {
                    return $query->where(['{{%division_services_map}}.category_id' => null]);
                }
            ])->deleted(false)
            ->where([
                'staff_id'    => $this->staff_id,
                'division_id' => $this->division_id
            ])
            ->orderBy('service_name ASC')
            ->all();

        if ($servicesWithoutCategories) {
            $categories[] = [
                'id'                 => null,
                'name'               => 'Без категории',
                'division_count'     => 0,
                'parent_category_id' => null,
                'services'           => $servicesWithoutCategories
            ];
        }

        return $categories;
    }

}
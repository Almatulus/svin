<?php

namespace api\modules\v2\search\newsLog;

use core\models\NewsLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * NewsLogSearch represents the model behind the search form about `core\models\newsLog`.
 */
class NewsLogSearch extends Model
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = NewsLog::find()
            ->enabled();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);

        return $dataProvider;
    }
}
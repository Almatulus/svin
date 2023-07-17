<?php

namespace api\modules\v2\search\company;

use core\models\finance\CompanyCash;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

/**
 * CompanyPaymentLogSearch represents the model behind the search form about `core\models\CompanyPaymentLog`.
 */
class CompanyCashSearch extends Model
{
    public $name;
    public $division_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function search($params)
    {
        $query = CompanyCash::find()->division()->active();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        if (!is_null($this->division_id)) {
            $query->division($this->division_id);
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
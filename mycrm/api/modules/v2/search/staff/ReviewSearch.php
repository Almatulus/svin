<?php

namespace api\modules\v2\search\staff;

use yii\data\ActiveDataProvider;
use core\models\StaffReview;
use yii\web\BadRequestHttpException;

/**
 * StaffReviewSearch represents the model behind the search form about `core\models\StaffReview`.
 */
class ReviewSearch extends StaffReview
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'staff_id'], 'integer'],
        ];
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
        $query = StaffReview::find()
                            ->andWhere(['status' => StaffReview::STATUS_ENABLED]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'customer_id' => $this->customer_id,
            'staff_id'    => $this->staff_id,
        ]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}

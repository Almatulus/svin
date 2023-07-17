<?php

namespace core\models;

use Yii;

/**
 * This is the model class for table "{{%cities}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $country_id
 *
 * @property Country $country
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cities}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'country_id' => Yii::t('app', 'Country ID')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'country_id' => 'country_id',
            'country_name' => function(City $model) {
                return $model->country->name;
            },
        ];
    }

    /**
     * @param $latitude
     * @param $longitude
     * @return int
     * @throws \Yandex\Geo\Exception
     * @throws \Yandex\Geo\Exception\CurlError
     * @throws \Yandex\Geo\Exception\ServerError
     */
    public static function getCityName($latitude, $longitude)
    {
        $api = new \Yandex\Geo\Api();

        // Можно искать по точке
        $api->setPoint($longitude, $latitude);

        // Настройка фильтров
        $api->setLimit(1) // кол-во результатов
            ->setLang(\Yandex\Geo\Api::LANG_RU) // локаль ответа
            ->load();

        $response = $api->getResponse();
        if($response->getFoundCount() > 0)
        {
            $collection = $response->getList();
            return $collection[0]->getLocalityName();
        }
        return null;
    }
}

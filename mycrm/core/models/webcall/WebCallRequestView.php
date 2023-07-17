<?php

namespace core\models\webcall;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\Url;

class WebCallRequestView
{

    private $_results = [];
    private $_headers = [];

    /**
     * @param array
     * @throws \Exception
     */
    public function __construct($results)
    {
        if (empty($results)) {
            throw new \Exception('Results in request are empty');
        }
        $this->_results = $results;
    }

    /**
     * Returns list of columns that user should see
     * @return array
     */
    private static function getUserVisibleColumns()
    {
        return [
            'direction' => [
                0 => Yii::t('app', 'Incoming call'),
                1 => Yii::t('app', 'Outgoing call')
            ],
            'client_number' => 'text',
            'start_time' => 'timestamp',
            'duration' => 'text',
            'answer_time' => 'timestamp',
            'recording' => 'link',
            'answered' => 'boolean'
        ];
    }

    /**
     * Returns converted string value
     * @param string $key
     * @param string $value
     * @return string
     * @throws \Exception
     */
    public static function parseValue($key, $value)
    {
        $columns = self::getUserVisibleColumns();
        if (!isset($columns[$key])) {
            throw new \Exception('User should not see column');
        }

        $column = $columns[$key];

        if (is_array($column)) {
            return $column[$value];
        } elseif ($column == 'timestamp') {
            return $value ? Yii::$app->formatter->asDatetime((new \DateTime())->setTimestamp($value)) : null;
        } elseif ($column == 'link') {
            return Html::tag('audio', Html::tag('source', '', ['src' => $value, 'type' => 'audio/mpeg']), ['controls' => 0]);
        } elseif ($column == 'boolean') {
            return Yii::t('app', 'Boolean ' . $value);
        } else {
            return Html::encode($value);
        }
    }

    /**
     * Returns results array
     * @return array
     */
    public function getResults() {
        return $this->_results;
    }

    /**
     * Returns list of columns user should see
     * @return array
     */
    public function getHeaders()
    {
        if (empty($this->_headers)) {
            $attributes = array_keys(get_object_vars($this->_results[0]));
            $visible_columns = array_keys(self::getUserVisibleColumns());
            $this->_headers = array_filter($attributes, function ($v, $k) use ($visible_columns) {
                return in_array($v, $visible_columns);
            }, ARRAY_FILTER_USE_BOTH);
        }
        return $this->_headers;
    }

}
<?php

namespace core\helpers;

use Yii;
use yii\helpers\BaseHtml;

class HtmlHelper extends BaseHtml {

    public static function getSummary(string $name = 'per-page', int $perPage = 20)
    {
        $perPage = Yii::$app->request->get($name, $perPage);
        $perPageDropdown = BaseHtml::dropDownList($name, $perPage, self::getPages(), [
            'class' => 'filter-per-page',
            'style' => 'margin-bottom: 5px'
        ]);
        return BaseHtml::tag('div', Yii::t('app', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}. | per page ') . $perPageDropdown);
    }

    public static function getPages() {
        return [
            20 => 20,
            50 => 50,
            100 => 100
        ];
    }
}
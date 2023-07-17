<?php

namespace core\helpers\color;

use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;

class ColorHtml extends BaseHtml
{
    public static function renderSelectOptions($selection, $items, &$tagOptions = [])
    {
        $lines = [];
        $encodeSpaces = ArrayHelper::remove($tagOptions, 'encodeSpaces', false);
        $encode = ArrayHelper::remove($tagOptions, 'encode', true);
        if (isset($tagOptions['prompt'])) {
            $prompt = $encode ? static::encode($tagOptions['prompt']) : $tagOptions['prompt'];
            if ($encodeSpaces) {
                $prompt = str_replace(' ', '&nbsp;', $prompt);
            }
            $lines[] = static::tag('option', $prompt, ['value' => '']);
        }

        $options = isset($tagOptions['options']) ? $tagOptions['options'] : [];
        unset($tagOptions['prompt'], $tagOptions['options'], $tagOptions['groups']);
        $options['encodeSpaces'] = ArrayHelper::getValue($options, 'encodeSpaces', $encodeSpaces);
        $options['encode'] = ArrayHelper::getValue($options, 'encode', $encode);

        foreach ($items as $key => $value) {
            $attrs = isset($options[$key]) ? $options[$key] : [];
            $attrs['value'] = (string)$key;
            if (is_array($value)) {
                foreach ($value as $attr_key => $attr_value) {
                    if ($attr_key == 'name') continue;
                    $attrs[$attr_key] = $attr_value;
                }
                $text = $encode ? static::encode($value['name']) : $value['name'];
            }
            $attrs['selected'] = $selection !== null &&
                (!is_array($selection) && !strcmp($key, $selection)
                    || is_array($selection) && in_array($key, $selection));
            if ($encodeSpaces) {
                $text = str_replace(' ', '&nbsp;', $text);
            }
            $lines[] = static::tag('option', $text, $attrs);
        }

        return implode("\n", $lines);
    }

}
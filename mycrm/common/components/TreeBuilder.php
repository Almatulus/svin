<?php

namespace common\components;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class TreeBuilder extends Widget {

    public $models;

    /**
     * attribute name for model's children
     */
    public $childrenAttribute;

    /**
     * edit buttons options
     * [class => ""]
     */
    public $editButtonOptions = ['class' => 'btn-edit'];

    /**
     * delete buttons options
     */
    public $deleteButtonOptions = ['class' => 'btn-delete'];

    public $editButtonVisible = true;

    public $deleteButtonVisible = true;


    public function run()
    {
        if (!empty($this->models)) {
            echo Html::tag("ul", $this->buildNodes($this->models), ['class' => 'subtree-simple-tree']);
        }
    }

    public function buildNodes($models)
    {
        $output = "";
        foreach ($models as $model) {
            $output .= Html::beginTag('li', ['class' => 'node', 'id' => $model->id]);
            $output .= $this->getHtmlItem($model);
            $children = $model->{$this->childrenAttribute};
            if (is_array($children)) {
                $output .= Html::beginTag('ul', ['class' => 'subtree-simple-tree']);
                $output .= $this->buildNodes($children);
                $output .= Html::endTag('ul');
            }
            $output .= Html::endTag('li');
        }
        return $output;
    }

    /**
     * @return string
     */
    public function getHtmlItem($model)
    {
        $content = Html::tag("span", "", ['class' => 'sort_handle']);
        $content .= $model->name;
        if ($this->editButtonVisible || $this->deleteButtonVisible) {
            $content .= Html::beginTag('div', ['class' => 'dropdown']);
            $content .= Html::tag('span', Html::tag('span', "", ['class' => 'caret']),
                ['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown']);
            $content .= Html::beginTag('ul', ['class' => 'dropdown-menu dropdown-menu-right']);

            if ($this->editButtonVisible) {
                $content .= Html::tag('li', Html::a(Yii::t('app', 'Edit'), "javascript:;", [
                    'class'    => $this->editButtonOptions['class'],
                    "data-url" => Url::to(['edit', 'id' => $model->id]),
                ]));
            }

            if ($this->deleteButtonVisible) {
                $content .= Html::tag('li', Html::a(Yii::t('app', 'Delete'), "javascript:;", [
                    "data-url" => Url::to(['delete', 'id' => $model->id]),
                    'class'    => $this->deleteButtonOptions['class']
                ]));
            }

            $content .= Html::endTag('ul');
            $content .= Html::endTag('div');
        }
        return Html::tag('div', $content, ['class' => 'item', 'title' => $model->name]);
    }
}
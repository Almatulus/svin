<?php

namespace common\components;

use Yii;
use yii\helpers\ArrayHelper;

class Model extends \yii\base\Model
{
    /**
     * Creates and populates a set of models.
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     */
    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model    = new $modelClass;
        $formName = $model->formName();
        $post     = Yii::$app->request->post($formName);
        $models   = [];

        if (! empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && !empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }

    /**
     * Saves new children models, delete old
     * @param $parentModel \yii\db\ActiveRecord
     * @param $children array
     * @param $parentAttr string
     * @param $attr string
     * @param $relation string
     * @return bool - successfully saved or not
     */
    public static function saveMultiple($parentModel, $children, $parentAttr, $attr, $relation) {
        $className = $parentModel->getRelation($relation)->modelClass;
        $oldValues     = ArrayHelper::getColumn($parentModel->{$relation}, $attr);
        $existedValues = $className::find()
                                      ->select($attr)
                                      ->where([$attr => $children, $parentAttr => $parentModel->id])
                                      ->asArray()
                                      ->all();
        $existedValues = ArrayHelper::getColumn($existedValues, $attr);

        if ($existedValues) {
            $oldValues = array_diff($oldValues, $existedValues);
            $children  = array_diff($children, $existedValues);
        }

        $className::deleteAll([$parentAttr => $parentModel->id, $attr => $oldValues]);

        foreach($children as $value) {
            $newModel              = new $className([
                $parentAttr => $parentModel->id,
                $attr => $value
            ]);
            if (!$newModel->save())
                return false;
        }
        return true;
    }
}
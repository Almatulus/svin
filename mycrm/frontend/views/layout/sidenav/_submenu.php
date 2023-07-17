<?php
use yii\helpers\Html;

?>
<ul>
    <?php foreach ($items as $item) { ?>
        <li>
            <?php
                $content = Html::tag('span', $item['label']);
                if (isset($items['icon'])) {
                    $content = Html::tag('i', '', ['class' => $item['icon']]) . $content;
                }
            ?>

            <?= Html::a($content, $item['url'], [
                'class' => $item['active'] ? 'active' : '',
                'title' => $item['label']
            ]); ?>

            <?php
                if (isset($item['items'])) {
                    echo $this->render('_submenu', ['items' => $item['items']]);
                }
            ?>
        </li>
    <?php } ?>
</ul>
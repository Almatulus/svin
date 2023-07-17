<?php
use yii\helpers\Url;

?>
<div class="column_row tree">
    <h4><?= $title ?></h4>
    <ul class="statistics_menu_list">
        <?php foreach ($items as $i => $item) {
            $active = ($item['active'] ? ' active' : '');
            $class = ($item['class'] ?? '') . $active;
            if ($view == 'tree' && $i == 0) {
                $active = ($item['active'] && $id != 'customers') ? 'active' : '';
            ?>
                <a class="item root <?= $active ?>" title="<?= $item['label'] ?>" href="<?= Url::to($item['url']) ?>">
                    <div class="icon_box">
                        <?= $item['label'] ?>
                    </div>
                </a>
                <!-- <ul> -->
            <?php } else { ?>
                <li>
                    <a class="<?= $class ?>" title="<?= $item['label'] ?>" href="<?= Url::to($item['url']) ?>">
                        <div class="icon_box">
                            <i class="<?= $item['icon'] ?>"></i>
                        </div>
                        <?= $item['label'] ?>
                    </a>
                    <?php
                    if (isset($item['items'])) {
                        echo $this->render('_submenu', ['items' => $item['items']]);
                    }
                    ?>
                </li>
            <?php } ?>
            <?php if ($view == 'tree') { ?>
                <!-- </ul> -->
            <?php } ?>
        <?php } ?>
    </ul>
</div>
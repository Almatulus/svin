<div class="column_row hidden-xs">
    <div class="progress_path">
        <?php    
        $steps = [
            1 => Yii::t('app', 'Begin stocktake'),
            2 => Yii::t('app', 'Count products'),
            3 => Yii::t('app', 'Stock levels summary'),
            4 => Yii::t('app', 'Stocktake sumary')
        ];
        foreach ($steps as $step => $label) { 
            $class = "";
            if ($model->status + 1 > $step) {
                $class = "selected";
            } elseif ($model->status + 1 == $step) {
                $class = "selected last_selected";
            }
            ?>
            <div class="step <?= $class ?>" style="width: 25%;">
                <?= $step != 1 ? '<div class="line left_line"></div>' : "" ?>
                <div class="content"><?= $label ?></div>
                <div class="point"><?= $step ?></div>
                <div class="line <?= $step != 4 ? "right_line" : ''?>"></div>
            </div>
        <?php } ?>
        <br class="c"><br>
    </div>
</div>
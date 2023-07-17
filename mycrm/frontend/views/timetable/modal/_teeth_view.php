<?php
/**
 * @var Company $company
 * @var array   $abbreviations
 */

use core\models\company\Company;
use core\models\ServiceCategory;

?>
<?php if ($company->category_id === ServiceCategory::ROOT_CLINIC): ?>
    <div class="order-tooth teeth-view details-row"
         data-abbreviations='<?= json_encode($abbreviations) ?>'
         data-colors='<?= json_encode($colors) ?>'>
        <div class="order-tooth-controls">
            <a class="show-child-tooth" href="javascript:;"
               data-title-on="Включить детские зубы"
               data-title-off="Выключить детские зубы" data-enabled="0"
               data-container='teeth-view'>
                Включить детские зубы
            </a>
        </div>
        <div class="row">
            <div class="col-sm-6 text-right">
                <?php for ($i = 18; $i >= 11; $i--) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <span class="tooth-number"><?= $i ?></span>
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>" data-company_customer_id="">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-sm-6">
                <?php for ($i = 21; $i <= 28; $i++) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <span class="tooth-number"><?= $i ?></span>
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row child-tooth-row" hidden>
            <div class="col-sm-6 text-right">
                <?php for ($i = 55; $i >= 51; $i--) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <span class="tooth-number"><?= $i ?></span>
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-sm-6">
                <?php for ($i = 61; $i <= 65; $i++) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <span class="tooth-number"><?= $i ?></span>
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 text-right">
                <?php for ($i = 48; $i >= 41; $i--) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                        <span class="tooth-number"><?= $i ?></span>
                    </div>
                <?php } ?>
            </div>
            <div class="col-sm-6">
                <?php for ($i = 31; $i <= 38; $i++) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                        <span class="tooth-number"><?= $i ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row child-tooth-row" hidden>
            <div class="col-sm-6 text-right">
                <?php for ($i = 85; $i >= 81; $i--) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                        <span class="tooth-number"><?= $i ?></span>
                    </div>
                <?php } ?>
            </div>
            <div class="col-sm-6">
                <?php for ($i = 71; $i <= 75; $i++) { ?>
                    <div class="order-tooth-wrapper js-teeth-history titled" data-teeth="<?= $i ?>"
                         title="<?= Yii::t('app', 'Click to see history') ?>">
                        <div class="order-tooth-img order-tooth-img-<?= $i ?>">
                            <img src="/image/teeth.png" width="35" height="35"/>
                        </div>
                        <span class="tooth-number"><?= $i ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <hr>
<?php endif; ?>
<div class="buttons-row pull-right ">
    <button class="btn btn-default js-create-medcard-tab btn-sm">Создать новую
        область
    </button>
</div>

<table class="table table-bordered table-condensed medcard-tabs">
    <thead>
    <tr>
        <th>Описание</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <td colspan="2">Ничего не выбрано</td>
    </tbody>
</table>
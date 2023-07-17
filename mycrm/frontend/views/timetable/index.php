<?php

use frontend\assets\FullcalendarPrintAsset;
use core\models\company\Company;
use core\forms\order\OrderForm;
use core\forms\order\PendingOrderForm;
use kartik\datetime\DateTimePicker;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/* @var $this View */
/* @var $staffs core\models\Staff[] */
/* @var $staffs_selected core\models\Staff[] */
/* @var $duration array example => ["min" => "08:00", "max" => "18:00"] */
/* @var $staff \core\models\Staff */

$this->title                   = Yii::t('app', 'Timetable');
$this->params['breadcrumbs'][] = $this->title;
$this->params['bodyID']        = 'calendar';

$selectedUserID   = isset($staffs[0]) ? $staffs[0]->id : null;
$selectedUsersArr = Json::encode(\yii\helpers\ArrayHelper::getColumn($staffs_selected, 'id'));

$duration['min'] = substr($duration['min'], 0, 5);
$duration['max'] = substr($duration['max'], 0, 5);

FullcalendarPrintAsset::register($this);
?>

<div class='timetable'>
    <?= Html::tag("div", "", [
        "id" => "timetable-calendar",
        "data-duration-min" => $duration['min'],
        "data-duration-max" => $duration['max']
    ]); ?>
	<div id="timetable-selected-users" class="hidden" data-selected-user="<?= $selectedUserID ?>"
		 data-selected-users="<?= $selectedUsersArr ?>"></div>
</div>


<?php
/* @var Company $company */
$company = Yii::$app->user->identity->company;
$company_logo = (empty($company->logo_id) || $company->logo_id === 1) ? null : $company->logo->getPath();
$this->registerJs(
    "var company_phone = '{$company->phone}'",
    View::POS_END
);
?>
    <div class="hidden" id="js-print-header">
        <?php foreach ($company->divisions as $division): ?>
            <?php if ( ! empty($division->logo_id)): ?>
                <img src="<?= $division->logo->getPath() ?>"
                     width="150" class="print-division-logo"
                     id="print-division-logo-<?= $division->id ?>"/>
                <br/>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($company_logo !== null): ?>
            <img src="<?= $company_logo ?>" width="150"/><br/>
        <?php endif; ?>
        <h2><?= $company->name ?></h2>
    </div>

<?php

$datepicker = DateTimePicker::widget([
    'name' => 'datetimepicker',
    'id' => 'order-datetimepicker',
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => Yii::t('app', 'Select date')],
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'dd MM yyyy hh:ii',
        'minuteStep' => Yii::$app->params['scheduleInterval'],
        'locale' => 'ru'
    ],
    'pluginEvents' => [
        'changeDate' => "function(e) {
            var time = moment.utc(e.date.valueOf()/1000, 'X').format('YYYY-MM-DD HH:mm');
            $('#order-datetime').val(time);
        }"
    ],
]);

$header = "<h4 class='modal-title inline_block'>Запись на <span class='modal-datetime-title'></span></h4>\n" .
		"<div class='modal-datetime inline_block' style='display:none'>" . $datepicker . "</div>" .
		"<div class='modal-datetime-controls inline_block'><a class='change-datetime' href='#'>изменить</a></div>";

$footer = Html::button(Yii::t('app', 'Create order'), ['class' => 'btn btn-primary pull-right save-order-button']) .
    Html::button(Yii::t('app', 'Print'), ['class' => 'btn btn-primary pull-right print-order-button', 'style' => 'display: none']) .
    Html::button(Yii::t('app', 'Checkout Order'), ['class' => 'btn btn-success pull-left checkout-order-button', 'style' => 'display: none']) .
    ButtonDropdown::widget([
      'label' => Yii::t('app', 'order status disabled'),
      'dropdown' => [
          'items' => [
              ['label' => 'Удалить из графика', 'url' => '#', 'options' => ['class' => 'delete-order-button']],
              ['label' => 'Оставить запись в графике', 'url' => '#', 'options' => ['class' => 'cancel-order-button']],
          ],
      ],
      'containerOptions' => ['class' => 'dropup pull-left'],
      'options' => ['class' => 'btn btn-danger disable-order-button', 'style' => 'display: none']
    ]) .
    Html::button(Yii::t('app', 'Return order'), ['class' => 'btn btn-danger pull-left return-order-button', 'style' => 'display: none']) .
    Html::button(Yii::t('app', 'Удалить из графика'), ['class' => 'btn btn-danger pull-left delete-order-button', 'style' => 'display: none']);


Modal::begin([
	"id" => "formModal",
	"size" => Modal::SIZE_LARGE,
	'header' => $header,
	'closeButton' => [
		'label' => Yii::t('app', 'Close'),
		'class' => 'btn btn-default pull-right close-order-button',
		'data-dismiss' => null
    ],
	"footer" => $footer,
	'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
	'options' => ['class' => 'order-modal', 'tabindex' => '', 'style' => 'overflow-y: auto;'],
    'footerOptions' => ['style' => 'background: #fff;']
]);

echo $this->render('_form', ['model' => new OrderForm(), 'staff' => $staff]);
Modal::end();

echo $this->render('modal/_med_card');

Modal::begin([
  "id" => "pending-order-modal",
  'closeButton' => [
    'label' => Yii::t('app', 'Close'),
    'class' => 'btn btn-default pull-right'
  ],
  'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
  'options' => ['tabindex' => '', 'style' => 'overflow-y: auto;'],
]);
echo $this->render('@app/modules/order/views/pending-order/_form', ['model' => new PendingOrderForm()]);
Modal::end();

echo $this->render("_print_order");

<?php

use core\helpers\TimetableHelper;
use core\models\Staff;
use yii\helpers\Html;

/* @var $staffs Staff[] */
/* @var $divisionsList array */
/* @var $selected_division_id integer */
/* @var $divisionOptions array */
/* @var $positionsList array */
?>

<div id="datepicker"></div>
<div id="entities_box">
    <div class="scrollable_list" style="height: 322px;">
        <div>
            <h3>Масштаб</h3>
            <?= Html::dropDownList(
                "interval",
                null,
                TimetableHelper::getIntervals(),
                [
                    'id'    => "timetable-interval",
                    'style' => 'margin-left: 5px; margin-bottom: 10px;'
                ]
            ); ?>
        </div>
        <div id="divisions_list">
            <h3>Филиал</h3>
            <?= Html::dropDownList(
                "timetable-division",
                null,
                $divisionsList,
                [
                    'id'      => "timetable-division_id",
                    'style'   => 'margin-left: 5px; margin-bottom: 10px;',
                    'options' => $divisionOptions
                ]
            ); ?>
        </div>
        <div id="divisions_list">
            <h3>Должность</h3>
            <?= Html::dropDownList(
                "timetable-company-position",
                null,
                $positionsList,
                [
                    'id'    => "timetable-position_id",
                    'style' => 'margin-left: 5px; margin-bottom: 10px;',
                    'prompt' => Yii::t('app', 'All company positions')
                ]
            ); ?>
        </div>
        <div id="users_list">
            <h3>Сотрудники</h3>
            <?php foreach ($staffs as $staff):
                foreach ($staff->divisions as $division):
                    if ( ! isset($divisionsList[$division->id])) {
                        continue;
                    }
                    
                    $checked = true;
                    $disabled = false;
                    if ($division->id != $selected_division_id) {
                        $checked  = false;
                        $disabled = true;
                    }

                    $position_names = implode(\core\models\company\CompanyPosition::STRING_DELIMITER,
                        $staff->getCompanyPositions()->select('name')->column());
                    if (!$position_names) {
                        $position_names = "Не задано";
                    }

                    $checkbox      = [
                        'class'                   => 'checkboxuser',
                        'data-entity-id'          => $staff->id,
                        'data-entity-division_id' => $division->id,
                        'data-entity-color'       => $staff->color,
                        'data-entity-name'        => $staff->getFullName(),
                        'data-entity-position'    => $position_names,
                        'disabled'                => $disabled
                    ];
                    ?>
                    <div class="entity_list_item"
                         data-employee-input-container="<?= $staff->id ?>">
                        <label class="bg-colors">
                            <?= Html::checkbox("entities[]", $checked,
                                $checkbox) ?>
                            <?= $staff->getFullName() ?>
                            <div class="<?= $staff->color ?> userbox"></div>
                        </label>
                    </div>
                    <?php
                endforeach;
            endforeach; ?>
        </div>
        <hr>
        <div id="waiting_list">
            <h3>Лист ожидания</h3>
            <button class="btn btn-default btn-sm js-add-pending-order" style="margin-left: 5px;">добавить в лист ожидания</button>
            <table class="table">
                <thead>
                    <tr>
                        <th>Пациент</th>
                        <th>Прием</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($waitingList as $key => $item) {
                        $date = new \DateTime($item->datetime);
                        $content = "<td>{$item->companyCustomer->customer->name}<br>{$item->companyCustomer->customer->phone}</td>" .
                            "<td>{$date->format('Y-m-d')}<br>{$item->staff->name}</td>";
                        echo Html::tag('tr', $content, [
                            'class' => 'draggable-order',
                            'data-id' => $item->id,
                            'data-event' => json_encode(array_merge($item->attributes, [
                                'customer_name' => $item->companyCustomer->customer->name,
                                'customer_phone' => $item->companyCustomer->customer->phone,
                                'division_id' => $item->division_id,
                                'title' => $item->companyCustomer->customer->name . "\n" . $item->companyCustomer->customer->phone
                            ]))
                        ]);
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

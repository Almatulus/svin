<?php

namespace core\forms\order;

use core\models\order\Order;
use core\models\Staff;
use core\repositories\StaffRepository;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property integer $staff
 * @property integer $start
 * @property Order $order
 */
class OrderMoveForm extends Model
{
    public $staff;
    public $start;
    public $order;

    private $staffRepository;

    public function __construct(Order $order, array $config = [])
    {
        $this->order = $order;
        $this->staffRepository = new StaffRepository();
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['staff', 'start'], 'required'],

            ['staff', 'integer'],

            ['start', 'date', 'format' => 'php:Y-m-d H:i:s'],

            [
                ['staff'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::className(),
                'targetAttribute' => ['staff' => 'id']
            ],
            ['staff', 'validateServices'],
        ];
    }

    public function validateServices($attribute, $params)
    {
        $staff = $this->staffRepository->find($this->staff);
        $staffServiceIds = ArrayHelper::getColumn($staff->divisionServices, 'id');
        $orderServices = $this->order->orderServices;
        foreach ($orderServices as $orderService) {
            if (!in_array($orderService->division_service_id, $staffServiceIds)) {
                $this->addError($attribute, Yii::t('app', 'Staff does not provide this service'));
            }
        }
    }

    public function formName()
    {
        return '';
    }
}

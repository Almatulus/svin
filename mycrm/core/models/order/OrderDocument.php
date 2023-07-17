<?php

namespace core\models\order;

use core\models\user\User;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%order_documents}}".
 *
 * @property integer $id
 * @property string $date
 * @property integer $order_id
 * @property string $path
 * @property integer $template_id
 * @property integer $user_id
 *
 * @property Order $order
 * @property OrderDocumentTemplate $template
 * @property User $user
 */
class OrderDocument extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_documents}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'order_id', 'path', 'template_id', 'user_id'], 'required'],
            [['date'], 'safe'],
            [['order_id', 'template_id', 'user_id'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderDocumentTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'date' => Yii::t('app', 'Date'),
            'order_id' => Yii::t('app', 'Order ID'),
            'template_id' => Yii::t('app', 'Template ID'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'date',
            'path',
            'link' => 'path',
            'templateName' => function() {
                return $this->template->name;
            },
            'userName' => function() {
                return $this->user->getFullName();
            },
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(OrderDocumentTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}

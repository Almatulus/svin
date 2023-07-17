<?php

namespace core\models\rbac;

use core\helpers\StaffHelper;
use core\models\user\User;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%auth_assignment}}".
 *
 * @property string   $item_name
 * @property string   $user_id
 * @property integer  $created_at
 *
 * @property AuthItem $itemName
 * @property User     $user
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'     => SaveRelationsBehavior::className(),
                'relations' => ['user'],
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_assignment}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Set user accesses
     *
     * @param array $roles
     * @param User  $user
     *
     * @return boolean
     */
    public static function setAccesses($roles, User $user)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            AuthAssignment::deleteAll(['user_id' => $user->id]);
            foreach ($roles as $role) {
                $assignment = new AuthAssignment([
                    'user_id'    => $user->id,
                    'item_name'  => $role,
                    'created_at' => time(),
                ]);
                if ( ! $assignment->save()) {
                    throw new Exception(Json::encode($user->getErrors()));
                }
            }

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();

            /** @var User $user */
            if (isset($related['user']) && $user = $related['user']) {
                $user->save();
                $this->user_id = $user->id;
            }
            return true;
        }
        return false;
    }

    /**
     * Returns accesses
     *
     * @param array $roles
     * @param User  $user
     *
     * @return AuthAssignment[]
     * @throws Exception
     */
    public static function getStaffAccesses(User $user, array $roles)
    {
        $staff_permissions = StaffHelper::getPermissionsList();
        return array_map(function (string $role) use ($user, $staff_permissions) {
            if (!in_array($role, $staff_permissions)) {
                throw new \DomainException($role . ' not allowed permission for staff');
            }

            $model       = new AuthAssignment([
                'item_name'  => $role,
                'created_at' => time(),
            ]);
            $model->populateRelation('user', $user);

            return $model;
        }, $roles);
    }
}

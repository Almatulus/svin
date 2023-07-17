<?php

use core\models\Staff;
use core\models\user\User;
use yii\db\Migration;

class m160920_090453_alter_users_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_users', 'key_confirm', $this->string());

        $data = [
            [
                'username' => 'sarra',
                'phone' => '+7 701 715 75 40'
            ],
            [
                'username' => 'beauty ville',
                'phone' => '+7 777 588 19 66'
            ],
        ];

        foreach ($data as $key => $userData) {
            $user = User::findOne(['username' => $userData['username']]);
            if ($user) {
                $user->username = $userData['phone'];
                if (!$user->save()) {
                    $errors = json_encode($user->errors);
                    echo $user->id . " {$errors}\n";
                }
            }
        }

        // set username from staff phone
        $staffs = Staff::find()->select(['id', 'user_id', 'phone'])->asArray()->all();
        
        $user_id = '';
        try {
            foreach ($staffs as $key => $staff) {
                if (isset($staff['user_id'])) {
                    $user = User::findOne($staff['user_id']);
                    $user_id = $staff['user_id'];
                    if ($user) {
                        if ($staff['phone']) {
                            $user->username = $staff['phone'];
                            if (!$user->save()) {
                                $errors = json_encode($user->errors);
                                echo $staff['user_id'] . " {$errors}\n";
                            }
                        } else {
                            echo $staff['user_id'] . " no phone\n";
                        }
                    } else {
                        echo $staff['user_id'] . " not found\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo $user_id . " {$e->getMessage()}\n";
        }

        unset($staffs);


        $users = User::find()->all();
        
        $template = "71110000000";
        $count = 0;
        foreach ($users as $key => $user) {

            if (!preg_match('/^\+(\d{1}) (\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $user->username, $matches)) {
                $phone = substr($template, 0, strlen($count) * (-1)) . $count;
                
                preg_match('/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches);

                $user->username = '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];

                $count++;

                if (!$user->save()) {
                    $errors = json_encode($user->errors);
                    echo $user->id . " {$errors}\n";
                }
            }
        }

    }

    public function safeDown()
    {
        $this->dropColumn('crm_users', 'key_confirm');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

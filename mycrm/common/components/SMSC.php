<?php

namespace common\components;

use yii\base\Component;

/**
 * Sending sms component
 * Other options available here: https://smsc.kz/api/
 *
 * @property string $login
 * @property string $password
 */
class SMSC extends Component
{
    public $login;
    public $password;

    /**
     * @param string $phone
     * @param string $message
     * @return mixed
     */
    public function send($phone, $message)
    {
        if (!($curl = curl_init())) {
            throw new \DomainException('CURL error');
        }

        $this->guardCredentials();

        $params = [
            'login' => $this->login,
            'psw' => $this->password,
            'charset' => 'utf-8',
            'phones' => $phone,
            'mes' => $message,
            'sender' => 'MYCRM.KZ',
            'cost' => 2, // get sms count and cost.
            'fmt' => 3 // get response in `json` format.
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://smsc.kz/sys/send.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    public function status($id, $phone)
    {
        if (!($curl = curl_init())) {
            throw new \DomainException('CURL error');
        }

        $this->guardCredentials();

        $params = [
            'login' => $this->login,
            'psw' => $this->password,
            'charset' => 'utf-8',
            'id' => $id,
            'phone' => $phone,
            'sender' => 'MYCRM.KZ',
            'cost' => 2, // get sms count and cost.
            'fmt' => 3 // get response in `json` format.
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://smsc.kz/sys/status.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    public function getStatusName($status)
    {
        return self::getStatusList()[$status];
    }

    public static function getStatusList()
    {
        return [
            -3 => 'Сообщение не найдено',
            -1 => 'Ожидает отправки',
            0  => 'Передано оператору',
            1  => 'Доставлено',
            2  => 'Прочитано',
            3  => 'Просрочено',
            20 => 'Невозможно доставить',
            22 => 'Неверный номер',
            23 => 'Запрещено',
            24 => 'Недостаточно средств',
            25 => 'Недоступный номер'
        ];
    }

    public function getErrorName($errorCode)
    {
        return 'Не доставлено - ' . self::getErrorList()[$errorCode];
    }

    public static function getErrorList()
    {
        return [
            1 => 'Ошибка в параметрах',
            2 => 'Неверный логин или пароль',
            3 => 'Недостаточно средств на счете Клиента',
            4 => 'IP-адрес временно заблокирован из-за частых ошибок в запросах',
            5 => 'Неверный формат даты',
            6 => 'Сообщение запрещено',
            7 => 'Неверный формат номера телефона',
            8 => 'Сообщение на указанный номер не может быть доставлено',
            9 => 'Отправка более одного одинакового запроса на передачу SMS-сообщения'
        ];
    }

    private function guardCredentials()
    {
        if (empty($this->login)) {
            throw new \DomainException('Login not set');
        }

        if (empty($this->password)) {
            throw new \DomainException('Password not set');
        }
    }
}
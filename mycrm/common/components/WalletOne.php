<?php

namespace common\components;

use Exception;
use Yii;
use yii\helpers\Url;

/**
 * Class that works with Wallet One Payment system
 */
class WalletOne
{
    /**
     * Checks wallet one payment request via signature
     * @return bool
     * @throws Exception
     */
    public static function checkRequest()
    {
        // Проверка наличия необходимых параметров в POST-запросе
        if (!isset($_POST["WMI_SIGNATURE"])) {
            throw new Exception('Отсутствует параметр WMI_SIGNATURE');
        }

        if (!isset($_POST["WMI_PAYMENT_NO"])) {
            throw new Exception('Отсутствует параметр WMI_PAYMENT_NO');
        }

        if (!isset($_POST["WMI_ORDER_STATE"])) {
            throw new Exception('Отсутствует параметр WMI_ORDER_STATE');
        }

        // Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE
        foreach ($_POST as $name => $value) {
            if ($name !== "WMI_SIGNATURE") $params[$name] = $value;
        }

        // Сортировка массива по именам ключей в порядке возрастания
        // и формирование сообщения, путем объединения значений формы

        uksort($params, "strcasecmp");
        $values = "";

        foreach ($params as $name => $value) {
            $values .= $value;
        }

        // Формирование подписи для сравнения ее с параметром WMI_SIGNATURE
        $signature = base64_encode(pack("H*", md5($values . Yii::$app->params['wallet_one_signature'])));

        //Сравнение полученной подписи с подписью W1
        if ($signature == $_POST["WMI_SIGNATURE"]) {
            if (strtoupper($_POST["WMI_ORDER_STATE"]) == "ACCEPTED") {
                return $_POST["WMI_PAYMENT_NO"];
            } else {
                // Случилось что-то странное, пришло неизвестное состояние заказа
                throw new Exception("Неверное состояние " . $_POST["WMI_ORDER_STATE"]);
            }
        } else {
            // Подпись не совпадает, возможно вы поменяли настройки интернет-магазина
            throw new Exception("Неверная подпись " . $_POST["WMI_SIGNATURE"]);
        }
    }

    /**
     * Generates fields for payment
     * @param $value
     * @param $currency
     * @param $id
     * @param $description
     * @param $customer_firstname
     * @return array
     */
    public static function generateFields($value, $currency, $id, $description, $customer_firstname)
    {
        $fields = array();

        // Добавление полей формы в ассоциативный массив
        $fields["WMI_MERCHANT_ID"] = \Yii::$app->params['wallet_one_id'];
        $fields["WMI_PAYMENT_AMOUNT"] = $value;
        $fields["WMI_CURRENCY_ID"] = $currency;
        $fields["WMI_PAYMENT_NO"] = $id;
        $fields["WMI_DESCRIPTION"] = "BASE64:" . base64_encode($description);
        $fields["WMI_EXPIRED_DATE"] = "2019-12-31T23:59:59";
        $fields["WMI_CUSTOMER_FIRSTNAME"] = $customer_firstname;
        if (YII_ENV != "test") {
            $fields["WMI_SUCCESS_URL"] = Url::to(['company/default/payment'], true);
            $fields["WMI_FAIL_URL"] = Url::to(['company/default/wallet-one-error', 's' => $id], true);
        }
        //Если требуется задать только определенные способы оплаты, раскоментируйте данную строку и перечислите требуемые способы оплаты.
        //$fields["WMI_PTENABLED"]      = array("UnistreamRUB", "SberbankRUB", "RussianPostRUB");

        //Сортировка значений внутри полей
        foreach ($fields as $name => $val) {
            if (is_array($val)) {
                usort($val, "strcasecmp");
                $fields[$name] = $val;
            }
        }

        //Добавление параметра WMI_SIGNATURE в словарь параметров формы
        $fields["WMI_SIGNATURE"] = self::generateSignature($fields);

        return $fields;
    }

    /**
     * Generates signature from fields
     * @param $fields
     * @return string
     */
    public static function generateSignature($fields)
    {
        // Формирование сообщения, путем объединения значений формы,
        // отсортированных по именам ключей в порядке возрастания.
        uksort($fields, "strcasecmp");
        $fieldValues = "";

        foreach ($fields as $value) {
            if (is_array($value))
                foreach ($value as $v) {
                    //Конвертация из текущей кодировки (UTF-8)
                    //необходима только если кодировка магазина отлична от Windows-1251
                    $v = iconv("utf-8", "windows-1251", $v);
                    $fieldValues .= $v;
                }
            else {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }

        // Формирование значения параметра WMI_SIGNATURE, путем
        // вычисления отпечатка, сформированного выше сообщения,
        // по алгоритму MD5 и представление его в Base64
        $signature = base64_encode(pack("H*", md5($fieldValues . Yii::$app->params['wallet_one_signature'])));

        return $signature;
    }
}
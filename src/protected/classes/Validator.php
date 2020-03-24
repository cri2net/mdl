<?php

class Validator
{
    public static function isEmpty($fieldName, $fieldValue)
    {
        if (empty($fieldValue)) {
            throw new Exception($fieldName. " - поле не повинно бути порожнім");
        }
    }

    public static function isMinLength($fieldName, $fieldValue, $minValue)
    {
        $fieldValue = trim($fieldValue);
        if (mb_strlen($fieldValue, 'UTF-8') < $minValue) {
            throw new Exception($fieldName.' '.str_replace('{count}', $minValue, " - не може бути менше, ніж {count} символа(ів)"));
        }
        return true;
    }

    public static function isMaxLength($fieldName, $fieldValue, $maxValue)
    {
        if (mb_strlen($fieldValue, 'UTF-8') > $maxValue) {
            throw new Exception($fieldName.' '.str_replace('{count}', $maxValue, " - не може бути більше, ніж {count} символів"));
        }
        return true;
    }

    public static function isEmail($fieldValue)
    {
        if (!filter_var($fieldValue, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Некорректно введений E-mail");
        }
    }

    public static function isNumeric($fieldName, $fieldValue)
    {
        if (!is_numeric($fieldValue)) {
            throw new Exception($fieldName.' - дане поле повинно містити тільки цифри');
        }
    }

    public static function isInRange ($fieldName, $fieldValue, $minValue, $maxValue){
        if ($fieldValue < $minValue || $fieldValue > $maxValue){
            throw new Exception($fieldName.' - значення має бути в діапазоні від '. $minValue. " до ".$maxValue);
        }
    }

    public static function isLength($fieldName, $fieldValue, $length){
        if (mb_strlen($fieldValue, 'UTF-8') !== $length){
            throw new Exception($fieldName.' - кількість символів має бути '. $length);
        }
    }

    public static function isNotNumeric($fieldName, $fieldValue)
    {
        if (is_numeric($fieldValue)) {
            throw new Exception($fieldName.' - дане поле не повинно містити цифр');
        }
    }

    public static function makeRightPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) == 12) {
            return '+'.$phone;
        }
        if (strlen($phone) == 11) {
            return '+3'.$phone;
        }
        if (strlen($phone) == 10) {
            return '+38'.$phone;
        }
        if (strlen($phone) == 9) {
            return '+380'.$phone;
        }
        return null;
    }

    public static function preparationArr($form_data)
    {
        foreach ($form_data as $key => $val) {

            if (is_array($val)) {
                $form_data[$key] = self::preparationArr($val);
                continue;
            }

            $form_data[$key] = trim($form_data[$key]);
            if ($key == 'password')continue;
            $form_data[$key] = strip_tags($form_data[$key]);
            $form_data[$key] = htmlspecialchars($form_data[$key]);
            $form_data[$key] = stripslashes($form_data[$key]);
        }
        return $form_data;
    }
}

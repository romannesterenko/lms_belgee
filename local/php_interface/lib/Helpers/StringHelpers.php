<?php


namespace Helpers;
use CUtil;

class StringHelpers
{

    public static function plural($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    public static function translit($string)
    {
        return CUtil::translit($string, 'ru', ["change_case"=>false, "replace_space" => ' ']);
    }

    public static function prepareTgLogin($login)
    {
        return str_replace('@', '', $login);
    }
    public static function generatePassword($length = 8){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    public static function parseCamelCaseToArray($str)
    {
        if(!is_string($str)||$str=='')
            return [];
        return preg_split("/((?<=[a-z])(?=[A-Z])|(?=[A-Z][a-z]))/", $str);
    }

    public static function preparePrice($price, $currency = "руб")
    {
        return number_format($price, 0, ".", " ")." ".$currency.".";
    }

    public static function unserialize(mixed $value)
    {
        $value = html_entity_decode($value, ENT_QUOTES);
        return unserialize($value);
    }
}
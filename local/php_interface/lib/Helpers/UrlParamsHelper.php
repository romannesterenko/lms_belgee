<?php


namespace Helpers;


class UrlParamsHelper
{

    public static function getParam(string $string)
    {
        $pieces = explode('?', $_REQUEST[$string]);
        return $pieces[0];
    }
}
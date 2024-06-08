<?php

namespace Helpers;

use Bitrix\Main\Loader;
use \Bitrix\Highloadblock\HighloadBlockTable;

class HLBlockHelper
{
    public static function initialize($table_name)
    {
        Loader::includeModule('highloadblock');
        $rsData = HighloadBlockTable::getList(array('filter' => array('TABLE_NAME' => $table_name)));
        if ($hldata = $rsData->fetch()) {
            $hlentity = HighloadBlockTable::compileEntity($hldata);
            return $hldata['NAME'] . 'Table';
        }
    }

    public static function get($dataClass, $filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return $dataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public static function add($fields, $dataClass)
    {
        $result = $dataClass::add($fields);
        return $result->isSuccess();
    }

    public static function update($id, $fields, $dataClass)
    {
        $result = $dataClass::update($id, $fields);
        return $result->isSuccess();
    }

    public static function delete($id, $dataClass)
    {
        $result = $dataClass::delete($id);
        return $result->isSuccess();
    }
}
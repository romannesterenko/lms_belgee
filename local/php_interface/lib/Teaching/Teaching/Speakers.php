<?php

namespace Teaching;

use Helpers\IBlockHelper;

class Speakers
{
    public static function getList($filter, $select = [], $sort = ['ID' => 'DESC'])
    {
        \CModule::IncludeModule('iblock');
        $list = [];
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getSpeakersIBlock()], $filter);
        $res = \CIBlockElement::GetList($sort, $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }
}
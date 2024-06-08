<?php


namespace Helpers;


class DealerHelper
{
    private static function getIblockId(){
        return \Helpers\IBlockHelper::getDealersIBlock();
    }
    public static function getTestDealerId(){
        return 360;
    }
    public static function getByUser($ID=0, $select = [])
    {
        $select = count($select)>0?array_merge(['ID'], $select):['ID', 'NAME', 'CODE', 'PROPERTY_COMM_NAME', 'PROPERTY_ENG_NAME', 'PROPERTY_CITY', 'PROPERTY_ORG_ADDRESS'];
        $ID = \Helpers\UserHelper::prepareUserId($ID);
        $dealer_id = \Helpers\UserHelper::getDealerId($ID);
        $array = [];
        if($dealer_id>0)
            $array = self::getList(['ID' => $dealer_id], $select);
        return count($array)>0?current($array):[];
    }
    public static function getList($filter, $select=[])
    {
        \CModule::IncludeModule('iblock');
        $list = [];
        $arFilter = array_merge(["IBLOCK_ID" => self::getIblockId()], $filter);
        $res = \CIBlockElement::GetList(array('NAME'=>'ASC'), $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getByCode($getValue)
    {
        return self::getList(['CODE' => $getValue], ['ID', 'NAME', 'CODE']);
    }
}
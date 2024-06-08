<?php
namespace Settings;
use CIBlockElement;
use CModule;
use Helpers\IBlockHelper;

class ZoomAccount{
    public static function getList($filter, $select=[])
    {
        if(!is_array($filter)||!is_array($select))
            return [];
        CModule::IncludeModule('iblock');
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getZoomAccountsIBlock(), 'ACTIVE' => 'Y'], $filter);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], $select));
        $list = [];
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getByCode($code)
    {
        if(empty($code))
            return [];
        $account = self::getList(['CODE' => $code], ['PROPERTY_CLIENT_ID','PROPERTY_CLIENT_SECRET','PROPERTY_REDIRECT_URL','PROPERTY_ACCESS_TOKEN','PROPERTY_REFRESH_TOKEN']);
        return check_full_array($account)?current($account):[];
    }

    private static function updateAccountFields($id, $code, $value)
    {
        IBlockHelper::includeIBlockModule();
        CIBlockElement::SetPropertyValuesEx($id, IBlockHelper::getZoomAccountsIBlock(), array($code => $value));
    }

    public static function setAccessToken($account_id, $access_token)
    {
        self::updateAccountFields($account_id, 'ACCESS_TOKEN', $access_token);
    }

    public static function setRefreshToken($account_id, $refresh_token)
    {
        self::updateAccountFields($account_id, 'REFRESH_TOKEN', $refresh_token);
    }

    public static function find($id)
    {
        if(empty($id)||(int)$id<=0)
            return [];
        $account = self::getList(['ID' => $id], ['PROPERTY_CLIENT_ID','PROPERTY_CLIENT_SECRET','PROPERTY_REDIRECT_URL','PROPERTY_ACCESS_TOKEN','PROPERTY_REFRESH_TOKEN']);
        return check_full_array($account)?current($account):[];
    }
}
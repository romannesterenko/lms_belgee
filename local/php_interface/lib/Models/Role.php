<?php

namespace Models;

use Helpers\IBlockHelper;
use Teaching\Roles;

class Role
{

    public static function getArray($array)
    {
        return \Teaching\Roles::getRolesList($array);
    }

    public static function getAllArray($array)
    {
        return \Teaching\Roles::getAllRolesList($array);
    }

    public static function getList($array, $select)
    {
        return \Teaching\Roles::getRolesList($array, $select);
    }

    public static function getTgGroups($deleted_groups)
    {
        $return_array = [];
        $list =  \Teaching\Roles::getRolesList(['ID' => $deleted_groups], ['PROPERTY_TG_CHANEL']);
        if(check_full_array($list)) {
            foreach ($list as $item)
                $return_array = array_merge($return_array, $item['PROPERTY_TG_CHANEL_VALUE']);
        }
        return $return_array;
    }

    public static function isOP($role_id):bool
    {
        $roles = Roles::getOPRoles();
        return !empty($roles[$role_id]);
    }

    public static function isMarketing($role_id):bool
    {
        $roles = Roles::getMarketingRoles();
        return !empty($roles[$role_id]);
    }

    public static function isPPO($role_id):bool
    {
        $roles = Roles::getPPORoles();
        return !empty($roles[$role_id]);
    }

    public static function getOPRoles()
    {
        return Roles::getOPRoles();
    }

    public static function getMarketingRoles()
    {
        return Roles::getMarketingRoles();
    }
    public static function getPPORoles()
    {
        return Roles::getPPORoles();
    }

    public static function getAll(array $array)
    {
        return Roles::getRolesList(['ACTIVE' => 'Y'], $array);
    }

    public static function getDeactivateFlagRoles()
    {
        $propertyEnumId = false;
        $rsEnum = \CIBlockPropertyEnum::GetList(
            array(),
            array(
                "IBLOCK_ID" => IBlockHelper::getRolesIBlock(),
                "XML_ID" => 'yes',
                "CODE"=>"DEACTIVATE_USER"
            )
        );
        if ($arEnum = $rsEnum->GetNext()) {
            $propertyEnumId = $arEnum["ID"];
        }
        return self::getList(['PROPERTY_DEACTIVATE_USER' => $propertyEnumId], ['ID', 'NAME']);
    }
}
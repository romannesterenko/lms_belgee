<?php
namespace Teaching\TestDrive;
use CIBlockElement;
use CModule;
use Helpers\UserHelper;

class Group
{
    public static function getList($filter, $select=[])
    {
        \CModule::IncludeModule('iblock');

        $list = [];

        $arFilter = array_merge(["IBLOCK_ID" => self::getIblockId()], $filter);
        global $USER;
        $select = array_merge(['ID'], $select);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getByUser($user_id = 0){
        $user_id = UserHelper::prepareUserId($user_id);
        $groups = self::getList(['=PROPERTY_USERS' => $user_id]);
        return check_full_array($groups)?array_keys($groups):[];
    }


    public static function deleteEmployeeFromGroup($schedule_id, $user_id)
    {
        $exist_groups = self::getList(['=PROPERTY_SCHEDULE' => $schedule_id, '=PROPERTY_USERS' => $user_id], ["ID", "PROPERTY_USERS"]);
        if(check_full_array($exist_groups)) {
            foreach ($exist_groups as $exist_group) {
                $new_users = array_values(array_unique(array_diff($exist_group['PROPERTY_USERS_VALUE'], [$user_id])));
                CIBlockElement::SetPropertyValuesEx($exist_group["ID"], false, array("USERS" => $new_users));
            }
        }
    }
    public static function setEmployeeToRandGroup($schedule_id, $user_id): void
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $exist_groups = self::getList(['=PROPERTY_SCHEDULE' => $schedule_id, '=PROPERTY_USERS' => $user_id], ["PROPERTY_USERS"]);
        if(check_full_array($exist_groups)) {
            //dump("Пользователь $user_id уже находится в группах ".implode('|', array_keys($exist_groups)));
        } else {
            $groups = self::getList(['=PROPERTY_SCHEDULE' => $schedule_id], ["PROPERTY_USERS"]);

            $min_group_array = [];
            $min_count = 99999;
            foreach ($groups as $_group){
                if(count($_group['PROPERTY_USERS_VALUE'])<=$min_count){
                    $min_group_array[count($_group['PROPERTY_USERS_VALUE'])][] = $_group['ID'];
                    $min_count = count($_group['PROPERTY_USERS_VALUE']);
                }
            }
            if(count($min_group_array)>1) {
                ksort($min_group_array);
            }
            $group_id = check_full_array($groups) ? current($min_group_array)[array_rand(current($min_group_array))] : false;
            if ($user_id > 0 && $group_id && check_full_array($groups[$group_id])) {
                $users_array = array_merge($groups[$group_id]['PROPERTY_USERS_VALUE'], [$user_id]);
                CIBlockElement::SetPropertyValuesEx($group_id, false, array("USERS" => array_unique($users_array)));
            }
        }
    }


    public static function getIblockId()
    {
        return TEST_DRIVE_GROUP_IBLOCK;
    }
}
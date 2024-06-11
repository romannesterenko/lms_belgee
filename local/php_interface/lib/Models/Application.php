<?php

namespace Models;

use CIBlockElement;
use CModule;
use Helpers\IBlockHelper;
use Notifications\EmailNotifications;
use Teaching\Roles;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
class Application {
    public static function getList($filter, $select = [])
    {
        if(!check_full_array($filter))
            return [];
        CModule::IncludeModule('iblock');
        $arFilter = array_merge(["IBLOCK_ID" => 35, 'ACTIVE' => 'Y'], $filter);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], $select));
        $list = [];
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getAll($select = []) {
        return self::getList(['>ID' => 0], $select);
    }

    public static function getLoadData($text_array, $user_id = 0)
    {
        if(check_full_array($text_array) && !empty($text_array['LOAD_FROM']) && $text_array['AUTOMATIC'] == 'on') {
            if ($text_array['LOAD_FROM'] == 'dealer') {
                if (!empty($text_array['DEALER_FIELDS'])) {
                    $dealer = Dealer::getCurrent([$text_array['DEALER_FIELDS'], 'PROPERTY_' . $text_array['DEALER_FIELDS']]);
                    if(check_full_array($dealer))
                        return ["HIDDEN" => $dealer[$text_array['DEALER_FIELDS']]??$dealer['PROPERTY_' . $text_array['DEALER_FIELDS'] . '_VALUE'], 'VALUE' => $dealer[$text_array['DEALER_FIELDS']]??$dealer['PROPERTY_' . $text_array['DEALER_FIELDS'] . '_VALUE']];
                }
            }
            if ($text_array['LOAD_FROM'] == 'user') {
                if (!empty($text_array['USER_FIELDS'])) {
                    switch ($text_array['USER_FIELDS']){
                        case "FIO":
                            $select = ["NAME", "LAST_NAME"];
                            if ($user_id == 0)
                                $user = User::getCurrent($select);
                            else
                                $user = User::getCurrentByID($user_id, $select);
                            if(check_full_array($user))
                                return ["HIDDEN" => $user['NAME']." ".$user['LAST_NAME'], 'VALUE' => $user['NAME']." ".$user['LAST_NAME']];
                            break;
                        case "UF_ROLE":
                            $role_ids = Roles::getByCurrentUser();
                            $roles = Roles::getById($role_ids);
                            return ["HIDDEN" => implode(", ", array_keys($roles)), 'VALUE' => implode(", ", $roles)];
                            break;
                        default:
                            if ($user_id == 0)
                                $user = User::getCurrent([$text_array['USER_FIELDS']]);
                            else
                                $user = User::getCurrentByID($user_id, [$text_array['USER_FIELDS']]);
                            if(check_full_array($user))
                                return ["HIDDEN" => $user[$text_array['USER_FIELDS']], 'VALUE' => $user[$text_array['USER_FIELDS']]];
                    }
                }
            }
        }
    }

    private static function generateDataClass($app_id)
    {
        CModule::IncludeModule('highloadblock');
        $filter = array(
            'NAME' => 'DealerApplication'.$app_id,
            'TABLE_NAME' => 'dealer_application'.$app_id
        );
        $hlblock = HLBT::getList(['filter' => $filter])->fetch();
        if(!check_full_array($hlblock))
            return false;
        return self::GetEntityDataClass($hlblock['ID']);
    }

    public static function getAddedItemsByDealer($app_id, $dealer_id)
    {

        $entity_data_class = self::generateDataClass($app_id);

        return $entity_data_class::getList(['filter' => ['UF_DEALER_ID' => $dealer_id]])->fetchAll();

    }

    public static function getNoneDeclinedAddedItemsByDealer($app_id, $dealer_id)
    {

        $entity_data_class = self::generateDataClass($app_id);

        return $entity_data_class::getList(['filter' => ['UF_DEALER_ID' => $dealer_id, 'UF_DECLINED' => false]])->fetchAll();

    }

    public static function getAllAdded($app_id)
    {

        $entity_data_class = self::generateDataClass($app_id);

        return $entity_data_class::getList([])->fetchAll();

    }
    public static function getAddedItems($app_id, $dealer_id, $user_id)
    {

        $entity_data_class = self::generateDataClass($app_id);

        return $entity_data_class::getList(['filter' => ['UF_DEALER_ID' => $dealer_id, 'UF_USER_ID' => $user_id]])->fetchAll();

    }

    public static function getApps($app_id, $filter)
    {
        $entity_data_class = self::generateDataClass($app_id);
        return $entity_data_class::getList(['filter' => $filter])->fetchAll();

    }

    public static function approveApplication($app_id, $record_id)
    {

        $entity_data_class = self::generateDataClass($app_id);
        if($entity_data_class)
            $entity_data_class::update($record_id, ['UF_APPROVED' => 1]);

    }

    public static function declineApplication($app_id, $record_id)
    {
        $entity_data_class = self::generateDataClass($app_id);
        if($entity_data_class)
            $entity_data_class::update($record_id, ['UF_DECLINED' => 1]);
        $app = $entity_data_class::getList(['filter' => ['ID' => $record_id]])->fetch();
        $application = current(Application::getList(['ID' => $app_id], ['ID', 'NAME', 'PROPERTY_ROLES']));
        $fields = [
            'USER_NAME' => User::getFullName($app['UF_USER_ID']),
            'APP_NAME' => $application['NAME'],
        ];
        $op_roles = Roles::getOPRoles();
        $ppo_roles = Roles::getPPORoles();
        $marketing_roles = Roles::getMarketingRoles();
        $is_need_op = false;
        $is_need_ppo = false;
        $is_need_marketing = false;
        $user = User::find($app['UF_USER_ID'], ['ID', 'NAME', 'UF_DEALER', 'UF_ROLE']);
        $admin_emails = [];
        if(check_full_array($user) && check_full_array($user['UF_ROLE'])){
            $need_roles = array_intersect($user['UF_ROLE'], $application['PROPERTY_ROLES_VALUE']);

            if(check_full_array($need_roles)){
                foreach($need_roles as $need_role){
                    if(in_array($need_role, array_keys($op_roles))){
                        $is_need_op = true;
                    }
                    if(in_array($need_role, array_keys($ppo_roles))){
                        $is_need_ppo = true;
                    }
                    if(in_array($need_role, array_keys($marketing_roles))){
                        $is_need_marketing = true;
                    }
                }
            }
            if ($is_need_op) {
                $admins = User::getOpAdmin($app['UF_USER_ID']);
                if (check_full_array($admins)) {
                    foreach ($admins as $key => $value) {
                        $admin_emails[] = $value['EMAIL'];
                        $admin_fields = [
                            'USER_NAME' => User::getFullName($value['ID']),
                            'APP_NAME' => $application['NAME'],
                            'EMPLOYEE_EMAIL' => User::getEmail($app['UF_USER_ID']),
                        ];
                        EmailNotifications::send('APPLICATION_WAS_DECLINED_FOR_ADMIN', $value['EMAIL'], $admin_fields);
                    }
                }
            }
            if ($is_need_ppo) {
                $admins = User::getPPOAdmin($app['UF_USER_ID']);
                if (check_full_array($admins)) {
                    foreach ($admins as $key => $value) {
                        $admin_emails[] = $value['EMAIL'];
                        $admin_fields = [
                            'USER_NAME' => User::getFullName($value['ID']),
                            'APP_NAME' => $application['NAME'],
                            'EMPLOYEE_EMAIL' => User::getEmail($app['UF_USER_ID']),
                        ];
                        EmailNotifications::send('APPLICATION_WAS_DECLINED_FOR_ADMIN', $value['EMAIL'], $admin_fields);
                    }
                }
            }
            if ($is_need_marketing) {
                $admins = User::getMarketingAdmin($app['UF_USER_ID']);
                if (check_full_array($admins)) {
                    foreach ($admins as $key => $value) {
                        $admin_emails[] = $value['EMAIL'];
                        $admin_fields = [
                            'USER_NAME' => User::getFullName($value['ID']),
                            'APP_NAME' => $application['NAME'],
                            'EMPLOYEE_EMAIL' => User::getEmail($app['UF_USER_ID']),
                        ];
                        EmailNotifications::send('APPLICATION_WAS_DECLINED_FOR_ADMIN', $value['EMAIL'], $admin_fields);
                    }
                }
            }
        }
        if(!in_array(User::getEmail($app['UF_USER_ID']), $admin_emails))
            EmailNotifications::send('APPLICATION_WAS_DECLINED', User::getEmail($app['UF_USER_ID']), $fields);
    }

    private static function GetEntityDataClass($HlBlockId) {
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = HLBT::getById($HlBlockId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    public static function updateRecord($app_id, $record_id, $fields)
    {
        $entity_data_class = self::generateDataClass($app_id);
        if($entity_data_class)
            $entity_data_class::update($record_id, $fields);
    }
}
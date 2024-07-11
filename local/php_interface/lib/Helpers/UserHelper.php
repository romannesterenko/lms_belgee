<?php

namespace Helpers;
use Bitrix\Main\Grid\Settings;
use Models\Dealer;
use Settings\Common;

class UserHelper
{
    private static $default_password = '123456789';
    public static function getRoleByCurrentUser()
    {
        global $USER;
        return self::getRoleByUser($USER->GetID());
    }

    public static function getRoleByUser($user_id)
    {
        $roles = self::getUserValue($user_id, 'UF_ROLE');
        if(check_full_array($roles))
            return $roles;
        return [];
    }

    public static function getList($filter = [], $select){
        $return_array = [];
        $filter = array_merge(['ACTIVE' => 'Y'], $filter);
        $rsUsers = \CUser::GetList(($by = "ID"), ($order = "desc"), $filter, ['SELECT' => $select]);
        while ($user = $rsUsers->Fetch()) {
            $return_array[] = $user;
        }
        if(count($return_array) > 1) {
            return $return_array;
        }
        if(count($return_array) == 1) {
            return current($return_array);
        }
        if(count($return_array) == 0) {
            return [];
        }
    }

    public static function getListArray($filter = [], $select){
        $return_array = [];
        $rsUsers = \CUser::GetList(($by = "ID"), ($order = "desc"), $filter, ['SELECT' => $select]);
        while ($user = $rsUsers->Fetch()) {
            $return_array[] = $user;
        }
        return count($return_array) == 0?[]:$return_array;
    }
    public static function getListSelect($filter = [], $select){
        $return_array = [];
        $rsUsers = \CUser::GetList(($by = "ID"), ($order = "desc"), $filter, $select);
        while ($user = $rsUsers->Fetch()) {
            $return_array[] = $user;
        }
        if(count($return_array) > 1) {
            return $return_array;
        }
        if(count($return_array) == 1) {
            return current($return_array);
        }
        if(count($return_array) == 0) {
            return [];
        }
    }

    public static function prepareUserId($user_id)
    {
        if ($user_id == 0) {
            global $USER;
            $user_id = $USER->GetID();
        }
        return $user_id;
    }

    public static function getUserValue($user_id, $string)
    {
        $fields = self::getList(['ID' => $user_id], [$string]);
        return $fields[$string];
    }

    public static function getShowMaterialsValue($user_id)
    {
        return self::getUserValue($user_id, 'UF_SHOW_MATERIALS');
    }
    private static function update($id, $fields){
        $user = new \CUser;
        if ($user->Update($id, $fields))
            return true;
        else
            return $user->LAST_ERROR;
    }

    private static function add($fields){
        $user = new \CUser;
        $ID = $user->Add($fields);
        if (intval($ID) > 0)
            return intval($ID);
        else
            return $user->LAST_ERROR;
    }
    public static function setUserValue($property, $value, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if (is_array($property)) {
            foreach ($property as $prop) {
                $fields[$prop] = $value;
            }
        } else {
            $fields[$property] = $value;
        }
        return self::update($user_id, $fields);

    }

    public static function updateShowMaterialsSettings(array $response)
    {
        if (self::setUserValue('UF_SHOW_MATERIALS', $response['values'], $response['user']))
            return true;
        return false;
    }

    public static function isTeachingAdmin($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getUserValue($user_id, 'UF_TEACHING_ADMIN_TYPE')!=false;
    }

    public static function isInDealersClub($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getUserValue($user_id, 'UF_DEALER_CLUB')==1;
    }

    public static function isLocalAdmin($user_id=0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getUserValue($user_id, 'UF_LOCAL_ADMIN')==1;
    }

    public static function isLocalAdminGMR($user_id=0)
    {
        global $USER;
        $user_id = self::prepareUserId($user_id);
        return $USER->GetID()==2||(self::getUserValue($user_id, 'UF_LOCAL_ADMIN')==1&&\Models\User::getDealerByUser()==Common::get('main_dealer'));
    }

    public static function getDealerId($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getUserValue($user_id, 'UF_DEALER')??0;
    }

    public static function getListByDealer($dealerId=0)
    {
        $dealerId = $dealerId>0?$dealerId:self::getDealerId();
        return self::getList(['UF_DEALER' => $dealerId], ['ID', 'NAME']);
    }

    public static function getCurUserId()
    {
        global $USER;
        return $USER->GetID();
    }

    public static function getByID($id=0)
    {
        $id = self::prepareUserId($id);
        return \Bitrix\Main\UserTable::getList(
            array(
                'filter' => array('ID' => $id), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                'select' => array('*', 'UF_*'), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                'order' => array('ID'=>'ASC'), // все группы, кроме основной группы администраторов,
                'limit' => 1
            )
        )->fetch();
    }

    public static function updateUserFields($request, $files)
    {
        $request = self::prepareRequestData($request);
        return self::update($request['user_id'], array_merge($request, $files));
    }
    private static function prepareRequestData($request) {
        if ($request['birthday_day']!='0'&&$request['birthday_month']!='0'&&$request['birthday_year']!='0')
            $request['PERSONAL_BIRTHDAY'] = $request['birthday_day'].'.'.$request['birthday_month'].'.'.$request['birthday_year'];
        else
            $request['PERSONAL_BIRTHDAY'] = false;
        if ($request['work_day']&&$request['work_month']&&$request['work_year'])
            $request['UF_WORK_START_DATE'] = $request['work_day'].'.'.$request['work_month'].'.'.$request['work_year'];
        $request['LOGIN'] = $request['EMAIL'];
        if($request['create']=='Y'){
            $request['PASSWORD'] = 123456789;
        }
        return $request;
    }
    public static function createUser($request, $files)
    {
        $request = self::prepareRequestData($request);
        return self::add(array_merge($request, $files));
    }

    public static function unsetUserDealer($id)
    {
        self::setUserValue('UF_DEALER', false, $id);
    }

    public static function updateFields($user_id, $request)
    {
        return self::update($user_id, $request);
    }

    public static function getTelegramId($recipient)
    {
        return self::getUserValue($recipient, 'UF_TELEGRAM');
    }

    public static function getFullName($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        $user = self::getList(['ID' => $user_id], ['NAME']);
        return $user['NAME'].' '.$user['LAST_NAME'];
    }

    public static function getEmail($user_id)
    {
        $user = self::getByID($user_id);
        return $user['EMAIL'];
    }

    /**
     * @return string
     */
    public static function getDefaultPassword()
    {
        return self::$default_password;
    }

    public static function generatePasswordForUser($user_id)
    {
        $pass = \Helpers\StringHelpers::generatePassword(8);
        if(self::update($user_id, ['PASSWORD' => $pass, 'CONFIRM_PASSWORD' => $pass]))
            return $pass;
    }

    public static function getIdsByCourse($course_id)
    {
        $ids = [];
        $list = self::getList(['UF_REQUIRED_COURSES' => $course_id], ['ID']);
        if($list['ID']>0)
            $ids[] = $list['ID'];
        else {
            foreach ($list as $user)
                $ids[] = $user['ID'];
        }
        return $ids;
    }

    public static function getIdByPhone($phone)
    {
        $array = self::getList(['PERSONAL_MOBILE' => $phone], ['ID']);
        return check_full_array($array)?$array['ID']:0;
    }

    public static function getPhoneByID($user_id)
    {
        $phone = self::getList(['ID' => $user_id], ['PERSONAL_MOBILE']);
        return check_full_array($phone)?$phone['PERSONAL_MOBILE']:'';
    }

    public static function getPPOAdminByUser($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getListArray(['ACTIVE' => 'Y', 'UF_DEALER' => Dealer::getByEmployee($user_id), 'UF_TEACHING_ADMIN_TYPE' => 16], ['ID', 'LAST_NAME','NAME']);
    }

    public static function getOPAdminByUser($user_id = 0)
    {
        $user_id = self::prepareUserId($user_id);
        return self::getListArray(['ACTIVE' => 'Y', 'UF_DEALER' => Dealer::getByEmployee($user_id), 'UF_TEACHING_ADMIN_TYPE' => 15], ['ID', 'LAST_NAME','NAME']);
    }
}
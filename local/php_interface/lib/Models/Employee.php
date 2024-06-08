<?php
namespace Models;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CUser;
use Helpers\Tasks;
use Helpers\UserHelper;
use Teaching\Roles;

class Employee
{
    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */



    public static function getEmployeesByAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        $dealer = User::getDealerByUser($user_id);
        if(!$dealer)
            return [];
        $roles = [];
        if(UserHelper::isTeachingAdmin($user_id))
            $roles = \Teaching\Roles::getRoleIdsByTeachingType(User::getTeachingType($user_id));
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_DEALER' => $dealer];
        if(count($roles)>0)
            $getListParams['filter']['UF_ROLE'] = $roles;
        return User::getArray($getListParams);
    }

    public static function getActiveEmployeesByAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        $dealer = User::getDealerByUser($user_id);
        if(!$dealer)
            return [];
        $roles = [];
        if(UserHelper::isTeachingAdmin($user_id))
            $roles = \Teaching\Roles::getRoleIdsByTeachingType(User::getTeachingType($user_id));
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_DEALER' => $dealer, "ACTIVE" => "Y"];
        if(count($roles)>0)
            $getListParams['filter']['UF_ROLE'] = $roles;
        return User::getArray($getListParams);
    }
    public static function getEmployeesByDealerAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        $dealer = User::getDealerByUser($user_id);
        if(!$dealer)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_DEALER' => $dealer];

        return User::getArray($getListParams);
    }
    public static function getActiveEmployeesByDealerAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        $dealer = User::getDealerByUser($user_id);
        if(!$dealer)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_DEALER' => $dealer, "ACTIVE" => "Y"];

        return User::getArray($getListParams);
    }


    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getEmployeesIdsByAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        if(!$user_id>0)
            return [];
        $user_ids = [];
        foreach(self::getEmployeesByAdmin($user_id) as $user)
            $user_ids[] = $user['ID'];
        return $user_ids;
    }
    public static function getActiveEmployeesIdsByAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        if(!$user_id>0)
            return [];
        $user_ids = [];
        foreach(self::getActiveEmployeesByAdmin($user_id) as $user)
            $user_ids[] = $user['ID'];
        return $user_ids;
    }
    public static function getEmployeesIdsByDealerAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        if(!$user_id>0)
            return [];
        $user_ids = [];
        foreach(self::getEmployeesByDealerAdmin($user_id) as $user)
            $user_ids[] = $user['ID'];
        return $user_ids;
    }
    public static function getActiveEmployeesIdsByDealerAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        if(!$user_id>0)
            return [];
        $user_ids = [];
        foreach(self::getActiveEmployeesByDealerAdmin($user_id) as $user)
            $user_ids[] = $user['ID'];
        return $user_ids;
    }

    public static function getByDealer($int)
    {
        $getListParams['select'] = ['ID', 'UF_TELEGRAM'];
        $getListParams['filter'] = ['UF_DEALER' => $int];
        return User::getArray($getListParams);
    }

    public static function getListByDealer($int, $select = ['*', 'UF_*'])
    {
        if(!$int>0) {
            $getListParams['filter'] = ['!UF_DEALER' => false];
        } else {
            $getListParams['filter'] = ['UF_DEALER' => $int];
        }
        $getListParams['select'] = $select;
        $list = User::getArray($getListParams);
        foreach ($list as &$user){
            if(!is_array($user['UF_ROLE']))
                $user['UF_ROLE'] = [];
        }
        return $list;
    }

    public static function getAttestatedByDealer(int $int)
    {
        $getListParams['select'] = ['ID', 'UF_TELEGRAM'];
        $getListParams['filter'] = ['UF_DEALER' => $int, '!UF_CERT_USER' => false];
        return User::getArray($getListParams);
    }

    public static function getNotAttestatedByDealer(int $int)
    {
        $getListParams['select'] = ['ID', 'UF_TELEGRAM'];
        $getListParams['filter'] = ['UF_DEALER' => $int, 'UF_CERT_USER' => false];
        return User::getArray($getListParams);
    }

    public static function setTgAttributes($updates)
    {
        foreach($updates['users'] as $user){
            $users = new CUser;
            //$bx_user = User::getByTgLogin('@'.$user['username']);
            $id = 2995;
            $aFields = array(
                'UF_ZOOM_LOGIN' => '123456789'
            );
            $users->Update($id, $aFields);
        }
    }

    public static function removeFromAllTGChannels($user_id)
    {
        $login = User::getTelegramLogin($user_id);
        if(!empty($login)) {
            $login = \Helpers\StringHelpers::prepareTgLogin($login);
            foreach (Roles::getChannelListFromRoleIds(Roles::getByUser($user_id)) as $channel) {
                Tasks::setRemoveUserFromTGChannelTask($login, $channel);
            }
        }
    }

    public static function getList($filter, $fields)
    {
        $getListParams['select'] = $fields;
        $getListParams['filter'] = $filter;
        $list = User::getArray($getListParams);
        foreach ($list as &$user){
            if(!is_array($user['UF_ROLE']))
                $user['UF_ROLE'] = [];
        }
        return $list;
    }

    public static function getDealerId($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $user = User::find($user_id, ['UF_DEALER']);
        return $user['UF_DEALER'];
    }

    public static function isTrainer($user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return self::getTrainerId($user_id)>0;
    }

    public static function getTrainerId($user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = \Teaching\Trainers::get(['PROPERTY_USER'=>$user_id]);
        $list = $list===[]?[]:current($list);
        return (int)$list['ID'];
    }

    public static function getByRole($role, $select)
    {
        $getListParams['select'] = $select;
        $getListParams['filter'] = ['UF_ROLE' => $role];
        $list = User::getArray($getListParams);
        foreach ($list as &$user){
            if(!is_array($user['UF_ROLE']))
                $user['UF_ROLE'] = [];
        }
        return $list;
    }

    public static function isExistsForZoom($email)
    {
        $getListParams['select'] = ['ID'];
        $getListParams['filter'] = ['=UF_ZOOM_LOGIN' => $email, '!UF_DEALER_CLUB' => false];
        $list = User::getArray($getListParams);

        if(check_full_array($list)){
            return $list[0]['ID']>0;
        }
        return false;
    }

    public static function isInReportsGroup()
    {
        return \CSite::InGroup( array(6) );
    }

    public static function unlinkFromDealer($dealer_id)
    {
        $users = self::getListByDealer($dealer_id);
        if( check_full_array($users)) {
            foreach ($users as $user) {
                User::resetDealer($user['ID']);
            }
        }
    }

    private static function setTgUserId($user)
    {
        $bx_user = User::getByTgLogin('@'.$user['username']);
        if((int)$bx_user['ID']>0) {
            User::setValue('UF_TG_ID', '2994', 2994);
        }
    }
}
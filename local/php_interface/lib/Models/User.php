<?php
namespace Models;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CUser;
use Helpers\StringHelpers;
use Helpers\Tasks;
use Helpers\UserHelper;
use Settings\Common;
use Teaching\CourseCompletion;
use Teaching\Enrollments;
use Teaching\Roles;

class User
{

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function find($id, $select=[])
    {
        $getListParams = ['limit'=>1];
        if($id>0)
            $getListParams['filter'] = ['=ID' => $id];
        if(count($select)>0) {
            $getListParams['select'] = array_merge(['ID'], $select);
        }
        $getList = UserTable::getList($getListParams)->fetch();
        return is_array($getList)?$getList:[];
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getByIds($id, $select=[])
    {
        $getListParams['filter'] = ['=ID' => $id];
        if(count($select)>0)
            $getListParams['select'] = $select;
        return UserTable::getList($getListParams)->fetchAll();
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getArray($params){
        try {
            if(!empty($params['filter']['ACTIVE'])) {
                if($params['filter']['ACTIVE']=='ALL') {
                    unset($params['filter']['ACTIVE']);
                }
            } else {
                $params['filter']['ACTIVE'] = 'Y';
            }
            return UserTable::getList($params)->fetchAll();
        }catch (Exception $e){
            return [];
        }
    }

    public static function needToHidePrice():bool
    {
        $dealer = UserHelper::getDealerId(UserHelper::prepareUserId(0));
        return Dealer::isHidePrice($dealer);
    }

    public static function isTeachingAdmin($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        return UserHelper::isTeachingAdmin($user_id);
    }
    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getUserIdsByFilter($filter){
        $getListParams['filter'] = $filter;
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER'];
        return self::getArray($getListParams);
    }
    public static function hasRightsToSet($user_id=0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        return UserHelper::isTeachingAdmin($user_id);
    }
    public static function hasRightsToEnrollEmployee($user_id=0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        return UserHelper::isTeachingAdmin($user_id)||UserHelper::isLocalAdmin($user_id);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getEmployeesByAdmin($GetID=0)
    {
        $user_id = UserHelper::prepareUserId($GetID);
        return Employee::getEmployeesByAdmin($user_id);
    }
    public static function getEmployeesIdsByAdmin($user_id=0)
    {
        $ids = [];
        foreach (self::getEmployeesByAdmin($user_id) as $employee)
            $ids[] = $employee['ID'];
        return $ids;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private static function getUserValue($user_id, $property_code){
        $user = self::find($user_id, [$property_code]);
        return $user[$property_code]??false;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getDealerByUser($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return self::getUserValue($user_id, 'UF_DEALER');
    }

    public static function getDealerName($user_id = 0) {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $dealer_id = self::getUserValue($user_id, 'UF_DEALER');
        if((int)$dealer_id > 0) {
            return Dealer::find($dealer_id)['NAME'];
        }
        return false;
    }


    //получаем всех пользователей кроме локальных админов и админов обучения
    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getNotAdminsEmployeesList()
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(10800, 'employees/not_admin')) {
            $result = $cache->getVars();
            //dump($result);
        } elseif ($cache->startDataCache()) {
            $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER'];
            $getListParams['filter'] = ['UF_LOCAL_ADMIN' => false, 'UF_TEACHING_ADMIN_TYPE'=>false];
            $result = self::getArray($getListParams);
            // Сохранение результатов в кеш
            $cache->endDataCache($result);
        }
        return $result;
    }


    //получаем пользователей, которым курс назначен лично
    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getEmployeesByCourse($course_id, $ids=false)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['UF_REQUIRED_COURSES' => $course_id];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }

    //получаем пользователей, которым курс назначен по ролям

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getEmployeesByRoleToCourse($course_id, $ids=false)
    {
        $return_roles = [];
        $roles = Roles::getRoleIdsForCourse($course_id);
        if(count($roles)==0||(int)$roles[0]<=0)
            return [];

        foreach ($roles as $role)
            $return_roles[] = $role;

        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['UF_ROLE' => $return_roles];

        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    //получаем пользователей, которым можно назначить курс

    /**
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function getEmployeesListForCourse($course_id)
    {
        //получаем всех пользователей кроме локальных админов и админов обучения
        $list = self::getNotAdminsEmployeesList();

        //убираем пользователей кому этот курс обязателен лично
        $users_req_course = self::getEmployeesByCourse($course_id);

        foreach ($list as $key => $item) {
            foreach ($users_req_course as $req_user) {
                if ($item['ID'] == $req_user['ID'])
                    unset($list[$key]);
            }
        }
        //убираем пользователей кому этот курс обязателен по ролям
        $users_req_roles = self::getEmployeesByRoleToCourse($course_id);
        foreach ($list as $key => $item){
            foreach ($users_req_roles as $role_req_user){
                if($item['ID']==$role_req_user['ID'])
                    unset($list[$key]);
            }
        }

        //возвращаем отсортированный список
        return $list;
    }
    public static function getNeedEmployeesListForCourse($course_id)
    {
        //получаем всех пользователей кроме локальных админов и админов обучения
        $list = self::getNotAdminsEmployeesList();
        //убираем пользователей кому этот курс обязателен лично
        $users_req_course = self::getEmployeesByCourse($course_id);
        //print_r($users_req_course);
        $all_user_ids = [];
        foreach ($list as $key => $item) {
            foreach ($users_req_course as $req_user) {
                if ($item['ID'] == $req_user['ID'])
                    $all_user_ids[] = $req_user['ID']; //unset($list[$key]);
            }
        }
        //убираем пользователей кому этот курс обязателен по ролям
        $users_req_roles = self::getEmployeesByRoleToCourse($course_id);
        //print_r($users_req_roles);
        foreach ($list as $key => $item){
            foreach ($users_req_roles as $role_req_user){
                if($item['ID']==$role_req_user['ID'])
                    $all_user_ids[] = $role_req_user['ID']; //unset($list[$key]);
            }
        }
        //print_r($all_user_ids);
        foreach($list as $k => $us){
            if(!in_array($us['ID'], $all_user_ids))
                unset($list[$k]);
        }
        //возвращаем отсортированный список
        return $list;
    }

    /**
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     */
    //получаем пользователей, которых можно записать на курс
    public static function getEmployeesListForCourseForEnroll($course_id, $admin_id=0)
    {
        //получаем всех пользователей кроме локальных админов и админов обучения
        $list = self::getNotAdminsEmployeesList();
        //возвращаем отосортированный список
        return $list;
    }
    //проверка на сертификацию сотрудника
    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function isCertifiedEmployee($user_id){
        return self::getUserValue($user_id, 'UF_CERT_USER')==1;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getCertifiedDate($user_id, $check_dates = false){
        $date = self::getUserValue($user_id, 'UF_CERT_USER_DATA');
        if($check_dates) {
            $diff = (time() - strtotime($date)) / 86400 / 365;
            return $diff > 2 ? '<span style="color:red">' . $date . '</span>' : $date;
        }
        return $date;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getDCAdminByUser(array $user)
    {
        $admin = [];
        if($user['UF_LOCAL_ADMIN']==0&&(int)$user['UF_DEALER']>0) {
            return self::getAdminByDealer($user['UF_DEALER']);
        }
        return $admin;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private static function getAdminByDealer($UF_DEALER)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_LOCAL_ADMIN', 'UF_DEALER'];
        $getListParams['filter'] = ['UF_DEALER' => $UF_DEALER, 'UF_LOCAL_ADMIN' =>1];
        $list = self::getArray($getListParams);
        return check_full_array($list)?current($list):[];
    }

    public static function getEmployeesByRoles($getRoleIdsForCourse)
    {
        if(count($getRoleIdsForCourse)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_LOCAL_ADMIN', 'UF_DEALER', 'UF_TELEGRAM', 'UF_ROLE'];
        $getListParams['filter'] = ['!UF_DEALER' => false, '!UF_ROLE' => false, 'UF_ROLE' => $getRoleIdsForCourse, '!UF_LOCAL_ADMIN' =>1];
        return self::getArray($getListParams);
    }

    public static function getByRole($role_id)
    {
        if(!(int)$role_id>0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_LOCAL_ADMIN', 'UF_DEALER', 'UF_TELEGRAM', 'UF_ROLE'];
        $getListParams['filter'] = ['!UF_DEALER' => false, 'UF_ROLE' => $role_id];
        return self::getArray($getListParams);
    }

    public static function getEmployeesIdsByRoles(array $getRoleIdsForCourse)
    {
        if(count($getRoleIdsForCourse)==0)
            return [];
        $ids = [];
        foreach (self::getEmployeesByRoles($getRoleIdsForCourse) as $user){
            $ids[] = $user['ID'];
        }
        return $ids;
    }

    public static function getEmployeesByRolesWithTG(array $getRoleIdsForCourse)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_LOCAL_ADMIN', 'UF_DEALER', 'UF_TELEGRAM'];
        $getListParams['filter'] = ['UF_ROLE' => $getRoleIdsForCourse, /*'!UF_LOCAL_ADMIN' =>1, */'!UF_TELEGRAM'=>false];
        return self::getArray($getListParams);
    }

    private static function collectIds($array)
    {
        $return_ids = [];
        foreach ($array as $item)
            $return_ids[] = $item['ID'];
        return $return_ids;
    }

    public static function getEnrolledEmployeesToCourse($course_id, $ids=false)
    {
        $enrolls = new Enrollments();
        $return_ids = [];
        foreach ($enrolls->getAllByCourseId($course_id) as $enroll){
                if(!(new CourseCompletion())->isDidntCom($enroll))
                    $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    public static function getEnrolledEmployeesToShedule($shedule_id, $ids=false)
    {
        $enrolls = new Enrollments();
        $return_ids = [];
        foreach ($enrolls->getAllBySheduleId($shedule_id) as $enroll){
                if(!(new CourseCompletion())->isDidntCom($enroll))
                    $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    public static function getCompletedEmployeesToCourse($course_id, $ids=false)
    {
        $enrolls = new CourseCompletion();
        $return_ids = [];
        foreach ($enrolls->getCompletedItemsByCourseID($course_id) as $enroll){
            $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    public static function getCompletedEmployeesToShedule($shedule_id, $ids=false)
    {
        $enrolls = new CourseCompletion();
        $return_ids = [];
        foreach ($enrolls->getCompletedItemsBySchedule($shedule_id) as $enroll){
            $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    public static function getCompletingEmployeesToCourse($course_id, $ids=false)
    {
        $enrolls = new CourseCompletion();
        $return_ids = [];
        foreach ($enrolls->getCompletingItemsByCourseID($course_id) as $enroll){
            $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }
    public static function getCompletingEmployeesToShedule($shedule_id, $ids=false)
    {
        $enrolls = new CourseCompletion();
        $return_ids = [];
        foreach ($enrolls->getCompletingItemsBySheduleID($shedule_id) as $enroll){
            $return_ids[] = $enroll['UF_USER_ID'];
        }
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['ID' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return self::getArray($getListParams);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getRecommendEmployeesByRoleToCourse($course_id, $ids=false)
    {
        $return_ids = Roles::getRoleIdsForCourse($course_id);
        if(count($return_ids)==0)
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['UF_ROLE' => $return_ids];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
        return [];
    }

    public static function getTeachingType($user_id)
    {
        return self::getUserValue($user_id, 'UF_TEACHING_ADMIN_TYPE');
    }

    public static function getByTgLogin($username)
    {
        $getListParams['select'] = ['ID', 'UF_TELEGRAM', 'UF_TG_ID', 'NAME', 'LAST_NAME', 'UF_ROLE'];
        $getListParams['filter'] = ['?UF_TELEGRAM' => $username];
        if(empty($username))
            return [];
        $list = self::getArray($getListParams);
        return array_shift($list);
    }

    public static function setField($user_id, string $string, $id)
    {
        UserHelper::setUserValue($string, $id, $user_id);
    }

    public static function getTelegramLogin($user_id)
    {
        $user = self::find($user_id, ['ID', 'UF_TELEGRAM', 'UF_TG_ID']);
        return $user['UF_TELEGRAM']??'';
    }

    public static function getTGChannelsByUser($user_id)
    {
        $login = self::getTelegramLogin($user_id);
        if(!empty($login)) {
            $login = StringHelpers::prepareTgLogin($login);
            foreach (Roles::getChannelListFromRoleIds(Roles::getByUser($user_id)) as $channel) {
                Tasks::setRemoveUserFromTGChannelTask($login, $channel);
            }
        }
    }

    public static function update($id, $fields){
        $user = new CUser;
        if ($user->Update($id, $fields))
            return true;
        else
            return $user->LAST_ERROR;
    }

    public static function setValue($property, $value, $user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        if (is_array($property)) {
            foreach ($property as $prop) {
                $fields[$prop] = $value;
            }
        } else {
            $fields[$property] = $value;
        }
        return self::update($user_id, $fields);

    }

    public static function getEmail($user_id)
    {
        $user = self::find($user_id, ['EMAIL']);
        return $user['EMAIL']??false;
    }

    public static function getDCAdmin($dealer_id, array $select=[])
    {
        $getListParams['select'] = array_merge(['ID', 'NAME', 'LAST_NAME'], $select);
        $getListParams['filter'] = ['UF_LOCAL_ADMIN' =>1, 'UF_DEALER'=>$dealer_id];
        return current(self::getArray($getListParams));
    }

    public static function getByVmsId($getValue)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $getListParams['filter'] = ['UF_VMS_ID' =>$getValue];
        return current(self::getArray($getListParams));
    }

    public static function getFullName(mixed $UD_USER_ID)
    {
        return UserHelper::getFullName($UD_USER_ID);
    }

    public static function create(array $fields)
    {
        $user = new CUser;
        $ID = $user->Add($fields);
        if (intval($ID) > 0)
            return $ID;
        else
            return $user->LAST_ERROR;
    }

    public static function getTeachingAdminTypes($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $types_sections = [];
        $user = self::find($user_id, ['ID', 'UF_TEACHING_ADMIN_TYPE']);
        if(!is_array($user)||!$user['ID']>0)
            return [];
        if(check_full_array($user['UF_TEACHING_ADMIN_TYPE'])){
            if(in_array(15, $user['UF_TEACHING_ADMIN_TYPE']) && Common::get('sale_admin_has_marketing_rights') == 'Y')
                $user['UF_TEACHING_ADMIN_TYPE'][] = 53;
            foreach ($user['UF_TEACHING_ADMIN_TYPE'] as $id) {
                $rsEnum = \CUserFieldEnum::GetList(
                    array(),
                    array(
                        "USER_FIELD_NAME" => "UF_TEACHING_ADMIN_TYPE",
                        "ID" => $id
                    )
                )->GetNext();
                if(is_array($rsEnum)&&$rsEnum['ID']==$id)
                    $types_sections[$rsEnum['ID']] = $rsEnum['XML_ID'];
            }
        }
        return $types_sections;
    }

    public static function getByEmail($email)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $getListParams['filter'] = ['EMAIL' =>$email];
        return current(self::getArray($getListParams));
    }

    public static function getByPartEmail($email)
    {
        $getListParams['select'] = ['ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_DEALER'];
        $getListParams['filter'] = ['ACTIVE' => 'N', '?EMAIL' =>$email];
        return self::getArray($getListParams);
    }

    public static function getByFullName($firstname, $lastname, $ids = [])
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $getListParams['filter'] = ['NAME' =>$firstname, 'LAST_NAME' => $lastname];

        $array = current(self::getArray($getListParams));
        if(!check_full_array($array)) {
            $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
            $getListParams['filter'] = ['NAME' => $lastname, 'LAST_NAME' => $firstname];
            return current(self::getArray($getListParams));
        }
        return $array;
    }

    public static function getArrayByFullName($firstname, $lastname)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $getListParams['filter'] = ['NAME' =>$firstname, 'LAST_NAME' => $lastname];

        $array = self::getArray($getListParams)??[];

        $getListParams['filter'] = ['NAME' => $lastname, 'LAST_NAME' => $firstname];
        foreach (self::getArray($getListParams) as $u){
            $array[] = $u;
        }

        return $array;
    }

    public static function getRegionalByFullName($firstname, $lastname, $dealer_id)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $getListParams['filter'] = ['NAME' =>$firstname, 'LAST_NAME' => $lastname];
        $array = self::getArray($getListParams)??[];
        $getListParams['filter'] = ['NAME' => $lastname, 'LAST_NAME' => $firstname];
        $replace_list = self::getArray($getListParams)??[];
        if(check_full_array($replace_list)){
            foreach ($replace_list as $us)
                $array[] = $us;
        }
        if($dealer_id>0){
            $dealer_info = \Models\Dealer::find($dealer_id, ['ID', 'CODE', 'NAME', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING']);
            foreach ($array as $current_user){
                if($dealer_info['PROPERTY_REGIONAL_VALUE']==$current_user['ID'])
                    return $current_user;
                if($dealer_info['PROPERTY_REGIONAL_PPO_VALUE']==$current_user['ID'])
                    return $current_user;
                if($dealer_info['PROPERTY_REGIONAL_MARKETING_VALUE']==$current_user['ID'])
                    return $current_user;
            }
        }
    }

    public static function getByFullNameAndCode($firstname, $lastname, $dealer_name, $dealer_code)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
        $dealer = Dealer::findOrCreate($dealer_name, $dealer_code);
        $getListParams['filter'] = ['NAME' =>$firstname, 'LAST_NAME' => $lastname, 'UF_DEALER' => $dealer];
        $array = current(self::getArray($getListParams));
        if(!check_full_array($array)) {
            $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
            $getListParams['filter'] = ['NAME' => $lastname, 'LAST_NAME' => $firstname, 'UF_DEALER' => $dealer];
            return current(self::getArray($getListParams));
        }
        return $array;
    }

    public static function getListByRole($role_id)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_TELEGRAM', 'UF_ROLE', 'UF_DEALER'];
        $getListParams['filter'] = ['UF_ROLE' =>$role_id, '!UF_DEALER' => false, 'ACTIVE' => 'Y'];
        return self::getArray($getListParams);
    }

    public static function deactivate($user_id)
    {
        self::setValue('ACTIVE', 'N', $user_id);
    }

    public static function activate($user_id)
    {
        self::setValue('ACTIVE', 'Y', $user_id);
    }

    public static function getTeachingAdminByUser($user_id=0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $users = UserHelper::getList(['UF_DEALER' => self::getDealerByUser($user_id), ['!UF_TEACHING_ADMIN_TYPE'=>Roles::getTeachingTypeByRole(Roles::getByUser())]], ['ID', 'NAME']);
        return $users;
    }

    public static function getTeachingAdminByCourseAndUser($course_id, $user_id=0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        if (Course::isOP($course_id)) {
            $users = UserHelper::getList(['UF_DEALER' => self::getDealerByUser($user_id), ['UF_TEACHING_ADMIN_TYPE' => 15]], ['ID', 'NAME']);
        } elseif(Course::isMarketing($course_id)) {
            $users = UserHelper::getList(['UF_DEALER' => self::getDealerByUser($user_id), ['UF_TEACHING_ADMIN_TYPE' => 53]], ['ID', 'NAME']);
            if(Common::get('sale_admin_has_marketing_rights') == "Y") {
                $op_users = UserHelper::getList(['UF_DEALER' => self::getDealerByUser($user_id), ['UF_TEACHING_ADMIN_TYPE' => 15]], ['ID', 'NAME']);
                $users = array_merge($users, $op_users);
            }
        } else {
            $users = UserHelper::getList(['UF_DEALER' => self::getDealerByUser($user_id), ['UF_TEACHING_ADMIN_TYPE' => 16]], ['ID', 'NAME']);
        }
        return $users;
    }

    public static function getEmployeesByMustRoleToCourse(mixed $course_id, $ids = false)
    {
        $role_ids = [];
        $role_array = Roles::getRolesForReqCourse($course_id);
        if(check_full_array($role_array)) {
            foreach ($role_array as $role)
                $role_ids[] = $role["ID"];
        }
        if(!check_full_array($role_ids))
            return [];
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['UF_ROLE' => $role_ids];
        return self::collectIds(self::getArray($getListParams));
    }

    public static function getById(int $int)
    {
        $params['SELECT'] = ["UF_*"];
        $params['FIELDS'] = [];
        $user = CUser::GetList('ID', 'DESC', ['ID' => $int], $params)->Fetch();
        if(check_full_array($user) && $user['ID']>0)
            return $user;
        return [];
    }


    public static function resetDealer($user_id)
    {
        self::setDealer($user_id, false);
    }


    public static function setDealer($user_id, $dealer)
    {
        self::update($user_id, ['UF_DEALER' => $dealer]);
    }

    public static function getUserRoles($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $user = self::getById($user_id);
        return $user['UF_ROLE']??[];
    }

    public static function getBySettedCourse($course_id, $ids = false)
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $getListParams['filter'] = ['UF_REQUIRED_COURSES' => $course_id];
        if($ids)
            return self::collectIds(self::getArray($getListParams));
    }

    public static function getAll()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE', 'EMAIL'];
        $getListParams['filter'] = ['>ID' => 0, 'ACTIVE' => 'ALL'];
        $list = self::getArray($getListParams);
        $return_array = [];
        foreach ($list as $item){
            $return_array[$item['ID']] = $item;
        }
        return $return_array;
    }

    public static function get($filter, $select = ['ID', 'NAME', 'EMAIL', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'])
    {
        $getListParams['select'] = $select;
        $getListParams['filter'] = $filter;

        $list = self::getArray($getListParams);
        $return_array = [];
        foreach ($list as $item){
            $return_array[$item['ID']] = $item;
        }
        return $return_array;
    }

    public static function getTeachingAdmins()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['!UF_TEACHING_ADMIN_TYPE' => false];
        return self::getArray($getListParams);
    }
    public static function getOPAdmins()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_TEACHING_ADMIN_TYPE' => 15];
        return self::getArray($getListParams);
    }
    public static function getPPOAdmins()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_TEACHING_ADMIN_TYPE' => 16];
        return self::getArray($getListParams);
    }

    public static function getWithDealers()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['!UF_DEALER' => false];
        return self::getArray($getListParams);
    }

    public static function getWithoutDealers()
    {
        $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE'];
        $getListParams['filter'] = ['UF_DEALER' => false];
        return self::getArray($getListParams);
    }

    public static function getFIOByTGLogin(mixed $tgLogin)
    {
        if(empty($tgLogin))
            return '';

        $user = self::getByTgLogin($tgLogin);
        return check_full_array($user)?$user['NAME']. " " .$user["LAST_NAME"]:'';

    }

    public static function getCurrent($select=[])
    {
        return self::find(UserHelper::prepareUserId(0), $select);
    }

    public static function getCurrentByID($id = 0, $select=[])
    {
        return self::find(UserHelper::prepareUserId($id), $select);
    }

    public static function getDirectionsByDealerAdmin($user_id = 0)
    {
        $replace = [
            16 => 'A01',
            15 => 'S01',
            53 => 'M01'
        ];
        $return_array = [];
        $user_id = UserHelper::prepareUserId($user_id);
        $user = self::find($user_id, ["ID", "UF_TEACHING_ADMIN_TYPE"]);
        if(!check_full_array($user["UF_TEACHING_ADMIN_TYPE"]))
            return $return_array;
        foreach ($user["UF_TEACHING_ADMIN_TYPE"] as $item) {
            $return_array[] = $replace[$item];
        }
        if(in_array('S01', $return_array) && Common::get('sale_admin_has_marketing_rights') == 'Y'){
            $return_array[] = 'M01';
        }
        return $return_array;
    }
    public static function getDirectionsByUser($user_id = 0)
    {
        $replace = [
            16 => 'A01',
            15 => 'S01',
            53 => 'M01'
        ];
        $return_array = [];
        $user_id = UserHelper::prepareUserId($user_id);
        $user = self::find($user_id, ["ID", "UF_TEACHING_ADMIN_TYPE"]);
        if(!check_full_array($user["UF_TEACHING_ADMIN_TYPE"]))
            return $return_array;
        foreach ($user["UF_TEACHING_ADMIN_TYPE"] as $item) {
            $return_array[] = $replace[$item];
        }
        return $return_array;
    }

    public static function getCurrentId()
    {
        return UserHelper::prepareUserId(0);
    }

    public static function getLevelList():array
    {
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_USER_RATING"));
        $enum = [];
        while($arEnum = $rsEnum->Fetch())
        {
            $enum[$arEnum["ID"]] = $arEnum["VALUE"];
        }
        return $enum;
    }
}
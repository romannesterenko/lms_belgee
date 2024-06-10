<?php

namespace Models;

use CIBlockElement;
use CIBlockSection;
use CModule;
use Helpers\IBlockHelper;
use Helpers\PropertyHelper;
use Helpers\UserHelper;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Tests;

class Course
{
    public static function isPaid($course_id)
    {
        return Courses::isPaid($course_id);
    }
    public static function find($id, $select=[]) {
        IBlockHelper::includeIBlockModule();
        $arFilter = ["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'ID' => $id];
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], $select));
        if ($ob = $res->GetNextElement())
            return $ob->GetFields();
        return [];
    }

    public static function getCourseCategory($course_id)
    {
        if(Course::isOP($course_id))
            return "S01";
        elseif (Course::isMarketing($course_id))
            return "M01";
        return "A01";
    }
    public static function setLastNumber(mixed $id, mixed $last_num)
    {
        self::updateCourseField($id, 'LAST_CERT_NUMBER', $last_num);
    }

    public static function resetLastNumber(mixed $id) {
        self::updateCourseField($id, 'LAST_CERT_NUMBER', 0);
    }

    public static function setRoles($id, $roles, $old_roles)
    {
        self::updateCourseField($id, 'ROLES', $roles);
        if(empty(\Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $id, 'OLD_ROLES')))
            self::updateCourseField($id, 'OLD_ROLES', implode('|', $old_roles));
    }

    public static function getBySection($ID)
    {
        if(!$ID>0)
            return [];
        return self::getList(['SECTION_ID' => $ID, 'INCLUDE_SUBSECTIONS' => 'Y', 'ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_COURSE_TYPE']);
    }

    public static function getByTeachingAdmin($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $teaching_admin_types = User::getTeachingAdminTypes($user_id);
        if(!is_array($teaching_admin_types)||count($teaching_admin_types)==0)
            return [];
        $return = [];

        foreach ($teaching_admin_types as $id => $type) {
            $section = current(\Helpers\IBlockHelper::getSections(['CODE' => $type], ['ID', 'NAME']));

            if(!is_array($section)||!$section['ID']>0)
                continue;
            $courses = self::getBySection($section['ID']);

            if(!check_full_array($courses))
                continue;
            foreach ($courses as $course){
                $return[$course['ID']] = $course;
            }
        }
        return $return;
    }

    public static function isOP($ID) {
        if(!is_numeric($ID) || $ID<=0)
            return false;
        $course = self::find($ID, ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
        if((int)$course['IBLOCK_SECTION_ID']>0){
            $nav = \CIBlockSection::GetNavChain(self::getIBlockID(), (int)$course['IBLOCK_SECTION_ID']);
            while ($result = $nav->fetch())
                if($result['DEPTH_LEVEL']==1&&$result['ID']>0)
                    return $result['ID']==4;

        }
        return false;
    }
    public static function isPPO($ID) {
        if(!is_numeric($ID) || $ID<=0)
            return false;
        $course = self::find($ID, ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
        if((int)$course['IBLOCK_SECTION_ID']>0){
            $nav = \CIBlockSection::GetNavChain(self::getIBlockID(), (int)$course['IBLOCK_SECTION_ID']);
            while ($result = $nav->fetch())
                if($result['DEPTH_LEVEL']==1&&$result['ID']>0)
                    return $result['ID']==17;

        }
        return false;
    }

    public static function isMarketing(int $ID)
    {
        if(!is_int($ID)||$ID<=0)
            return false;
        $course = self::find($ID, ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
        if((int)$course['IBLOCK_SECTION_ID']>0){
            $nav = \CIBlockSection::GetNavChain(self::getIBlockID(), (int)$course['IBLOCK_SECTION_ID']);
            while ($result = $nav->fetch())
                if($result['DEPTH_LEVEL']==1 && $result['ID']>0) {
                    return $result['ID'] == 138;
                }

        }
        return false;
    }

    public static function getIBlockID()
    {
        return COURSES_IBLOCK;
    }

    public static function isActive(mixed $UF_COURSE_ID)
    {
        $course = self::find($UF_COURSE_ID, ['ID', 'ACTIVE']);
        return is_array($course)&&(int)$course['ID']>0&&$course['ACTIVE']=='Y';
    }

    public static function getTotalAttempts($course_id)
    {
        if($course_id==0)
            return 0;
        $course = self::find($course_id, ['ID', 'PROPERTY_MAX_ATTEMPTS']);
        if(check_full_array($course))
            return (int)$course['PROPERTY_MAX_ATTEMPTS_VALUE'];
        return 0;
    }

    public static function needCoupon($course_id=0)
    {
        if($course_id==0)
            return false;
        return \Teaching\Courses::isPaid($course_id);
    }

    public static function isScormCourse($course_id)
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'SCORM')!=false;
    }

    public static function ScormCourseHasRetest($course_id)
    {
        $test = Tests::getTestByCourse($course_id, ['ID', 'PROPERTY_RETEST_ONLY']);
        if(!check_full_array($test))
            return false;
        $test = current($test);
        return $test['PROPERTY_RETEST_ONLY_ENUM_ID'] == 164;
    }

    public static function isAllowToEnrollUser($course_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $max_dc_users = self::getMaxDCUsers($course_id);
        if($max_dc_users==0)
            return true;
        $user = current(\Models\User::getArray(['filter' => ['ID' => $user_id], 'select' => ['ID', 'UF_DEALER']]));

        if(check_full_array($user)&&(int)$user['UF_DEALER']>0){
            //dump($user);
            $dealers = self::getApprovedUsers($course_id);
            if( !empty($dealers[$user['UF_DEALER']]) ) {
                return count($dealers[$user['UF_DEALER']])<$max_dc_users;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function isHasMaxUsers($course_id)
    {
        return (int)\Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'MAX_DC_EMPLS')>0;
    }

    public static function getMaxDCUsers($course_id)
    {
        return (int)\Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'MAX_DC_EMPLS');
    }

    public static function getApprovedUsers($course_id)
    {
        $schedules = \Teaching\SheduleCourses::getByCourse($course_id);
        //dump($schedules);
        $main_array = [];
        if(check_full_array($schedules)){
            foreach($schedules as $schedule) {
                $users_by_schedule = \Teaching\SheduleCourses::getApprovedUsers($schedule['ID']);
                if (check_full_array($users_by_schedule)) {
                    foreach ($users_by_schedule as $dealer_id => $users) {
                        if (check_full_array($main_array[$dealer_id])) {
                            $main_array[$dealer_id] = array_merge($main_array[$dealer_id], $users);
                            $main_array[$dealer_id] = array_unique($main_array[$dealer_id]);
                        } else {
                            $main_array[$dealer_id] = $users;
                        }
                    }
                }
            }
        }
        return $main_array;
    }

    public static function getByRole($role, $ids = false):array {
        if($ids){
            $list = self::getList(['PROPERTY_ROLES' => $role], ['ID', 'PROPERTY_ROLES']);
            $ids_array = [];
            foreach ($list as $item)
                $ids_array[] = $item['ID'];
            $role = current(Role::getList(['ID' => $role], ['PROPERTY_COURSES']));
            if(check_full_array($role['PROPERTY_COURSES_VALUE']))
                $ids_array = array_merge($ids_array, $role['PROPERTY_COURSES_VALUE']);
            return array_unique($ids_array);
        } else {
            $role_array = current(Role::getList(['ID' => $role], ['PROPERTY_COURSES']));
            $must_have_courses = [];
            if(check_full_array($role_array['PROPERTY_COURSES_VALUE']))
                $must_have_courses = self::getList(['ID' => $role_array['PROPERTY_COURSES_VALUE']], ['ID', 'PROPERTY_ROLES']);
            $common_courses =  self::getList(['PROPERTY_ROLES' => $role], ['ID', 'PROPERTY_ROLES']);
            $return_courses = [];
            if(check_full_array($must_have_courses)) {
                foreach ($must_have_courses as $must_have_course)
                    $return_courses[$must_have_course['ID']] = $must_have_course;
            }
            if(check_full_array($common_courses)) {
                foreach ($common_courses as $common_course) {
                    if(!check_full_array($return_courses[$common_course['ID']]))
                        $return_courses[$common_course['ID']] = $common_course;
                }
            }
        }
    }
    public static function getMustByRole($role, $ids = false):array
    {
        if((int)$role<=0)
            return [];
        $role = current(Role::getList(['ID' => $role], ['PROPERTY_COURSES']));
        if($ids){
            $ids_array = [];
            if(check_full_array($role['PROPERTY_COURSES_VALUE']))
                $ids_array = array_merge($ids_array, $role['PROPERTY_COURSES_VALUE']);
            return array_unique($ids_array);
        } else {
            $must_have_courses = [];
            if(check_full_array($role['PROPERTY_COURSES_VALUE']))
                $must_have_courses = self::getList(['ID' => $role['PROPERTY_COURSES_VALUE']], ['ID', 'PROPERTY_ROLES']);
            $return_courses = [];
            if(check_full_array($must_have_courses)){
                foreach ($must_have_courses as $must_have_course)
                    $return_courses[$must_have_course['ID']] = $must_have_course;
            }
            return $return_courses;
        }
    }

    public static function isIgnoreStatus($course_id)
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'IGNORE_STATUS')==122;
    }

    public static function getIgnorePoints($course_id, $part)
    {

        $array = \Helpers\PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'COMPLETE_POINTS');
        return (int)$array[$part];
    }

    public static function getByName(mixed $cname)
    {
        return current(self::getList(['NAME' => $cname, 'ACTIVE' => 'ALL'], ['ID', 'NAME']));
    }

    public static function getOPList($ids = false)
    {
        $list = self::getList(['SECTION_ID' => 4, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID', 'NAME']);
        if($ids){
            $array_ids = [];
            foreach ($list as $item)
                $array_ids[] = $item['ID'];
            return $array_ids;
        }
        return $list;
    }
    public static function getPPOList($ids = false, $na=false)
    {
        $filter = ['SECTION_ID' => 17, 'INCLUDE_SUBSECTIONS' => 'Y'];
        if($na)
            $filter["ACTIVE"] = "N";
        $list = self::getList($filter, ['ID', 'NAME']);
        if($ids){
            $array_ids = [];
            foreach ($list as $item)
                $array_ids[] = $item['ID'];
            return $array_ids;
        }
        return $list;
    }
    public static function getMarketingList($ids = false, $na=false)
    {
        $filter = ['SECTION_ID' => 138, 'INCLUDE_SUBSECTIONS' => 'Y'];
        if($na)
            $filter["ACTIVE"] = "N";
        $list = self::getList($filter, ['ID', 'NAME']);
        if($ids){
            $array_ids = [];
            foreach ($list as $item)
                $array_ids[] = $item['ID'];
            return $array_ids;
        }
        return $list;
    }

    public static function getMaxPoints(mixed $ID):int
    {
        if(self::isScormCourse($ID))
            return 100;
        $test = current(Tests::getTestByCourse($ID, ['ID', 'PROPERTY_POINTS']));
        if((int)$test['PROPERTY_POINTS_VALUE'] > 0)
            return $test['PROPERTY_POINTS_VALUE'];
        //минимальная сумма для прохождения ретеста - 80%
        return Tests::getMaxPointsByCourse($ID)*0.8;
    }

    public static function isFreeEntrance(mixed $ID):bool
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $ID, 'COURSE_TYPE')==6;
    }

    public static function getMinPoints(mixed $ID):int
    {
        if(self::isFreeEntrance($ID)){
            $test = Tests::getTestByCourse($ID);
            if($test['ID']>0)
                return Tests::getMinPointsForComplete($test['ID']);
        }
        return 0;
    }

    public static function hasIncomingTest($course_id):bool
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'IS_IN_TEST')==128;
    }

    public static function hasUncompletingIncomingTest($course_id):bool
    {
        $user = UserHelper::prepareUserId(0);
        $current_completion = current((new CourseCompletion())->get(['UF_COURSE_ID' => $course_id, 'UF_USER_ID' => $user, 'UF_IS_COMPLETE' => false]));
        if(!check_full_array($current_completion))
            return false;
        return $current_completion['UF_PRETEST_PROCESS']!=1;
    }

    public static function isWithShedule()
    {

    }

    public static function getBeforeCourses($course_id)
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'NEED_COURSES_BEFORE');
    }

    public static function getAccessToEnroll($user_id, $course_id)
    {
        $user = User::find($user_id, ['UF_DEALER']);
        $category = self::getCourseCategory($course_id);
        $balance = \Models\Dealer::getBalance($user["UF_DEALER"]);
        return (int)$balance[$category]['free']>0;
    }

    public static function getCost($course_id)
    {
        return \Helpers\PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'COST');
    }

    public static function getStatus($course_id, $user_id = 0)
    {
        if(Courses::isCompleted1($course_id, $user_id))
            return 'completed';
        if(Courses::isExpired($course_id, $user_id)) {
            if(Courses::isNeedRetest($course_id))
                return 'expired';
            else
                return 'expired_date';
        }
        return 'uncompleted';
    }

    public static function getStatusByCompletion($course_id, $user_id, $completion_id)
    {
        if((new CourseCompletion())->isCompleted1($course_id, $user_id, $completion_id))
            return 'completed';
        if((new CourseCompletion())->isExpired($course_id, $user_id, $completion_id)) {
            if(Courses::isNeedRetest($course_id))
                return 'expired';
            else
                return 'expired_date';
        }
        return 'uncompleted';
    }


    public static function isEvent(mixed $course_id)
    {
        return PropertyHelper::getPropertyValue(self::getIblockId(), $course_id, 'EVENTS_COURSE')==154;
    }

    public static function isTestCourse($course_id)
    {
        //TODO: Отключить вызов этого метода везде
        return true;
    }

    public static function isBalancePayment($course_id):bool
    {
        $all_values = PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'PAY_METHOD');

        return count($all_values) == 1 && in_array(160, $all_values);
    }

    public static function hasBalancePayment($course_id):bool
    {
        $all_values = PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'PAY_METHOD');
        return in_array(160, $all_values);
    }

    public static function isMultiplePayment($course_id):bool
    {
        $all_values = PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'PAY_METHOD');
        //dump($all_values);
        return count($all_values) > 1;
    }

    public static function isAllPayment($course_id)
    {
        $all_properties = Course::getPaymentMethodsList();
        $all_values = PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'PAY_METHOD');
        return count(array_diff(array_keys($all_properties), $all_values))==0;
    }

    public static function isCertificatePayment($course_id)
    {
        $all_values = PropertyHelper::getPropertyValues(self::getIblockId(), $course_id, 'PAY_METHOD');
        return count($all_values) == 1 && in_array(159, $all_values);
    }

    public static function getPaymentMethodsList()
    {
        return PropertyHelper::getPropertyValuesList(self::getIblockId(), 'PAY_METHOD');
    }

    public static function getExpiredDate(mixed $ID)
    {
        return PropertyHelper::getPropertyValue(self::getIblockId(), $ID, 'CERT_EXP')??12;
    }

    public static function getAll($select = [])
    {
        return self::getList(['ACTIVE' => 'Y'], $select);
    }

    public static function getDirrection(mixed $ID)
    {
        $arElement = CIBlockElement::GetByID($ID)->GetNext();
        if ($arElement) {
            $sectionID = $arElement['IBLOCK_SECTION_ID'];
            $topSection = self::getTopSection($sectionID);

            if ($topSection) {
                return $topSection['NAME'];
            } else {
                echo "";
            }
        } else {
            echo "";
        }
    }
    private static function getTopSection($sectionID) {
        $arSection = CIBlockSection::GetByID($sectionID)->GetNext();
        if ($arSection) {
            // Если у раздела есть родительский раздел, рекурсивно вызываем эту функцию для него
            if ($arSection['IBLOCK_SECTION_ID'] > 0) {
                return self::getTopSection($arSection['IBLOCK_SECTION_ID']);
            } else {
                return $arSection;
            }
        } else {
            return false;
        }
    }

    public static function getTemplateXML(int $int)
    {
        CModule::IncludeModule('iblock');
        $prop = \Helpers\PropertyHelper::getPropertyValue(\Models\Course::getIBlockID(), $int, 'CERTIFICATE_TEMPLATE');
        if ($prop > 0){
            $rsEnum = \CIBlockPropertyEnum::GetList(
                array(),
                array(
                    "IBLOCK_ID" => \Models\Course::getIBlockID(),
                    "ID" => $prop
                )
            )->Fetch();
            return $rsEnum['XML_ID'];
        }
        return '';
    }

    public function activate($id){
        $list = self::getList(['SECTION_ID' => 17, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID']);
    }

    public static function getList($filter, $select)
    {
        if(!is_array($filter)||!is_array($select))
            return [];
        CModule::IncludeModule('iblock');
        if(array_key_exists('ACTIVE', $filter) && $filter['ACTIVE'] == 'N'){
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'PROPERTY_TEST' => false], $filter);
        } elseif (array_key_exists('ACTIVE', $filter) && $filter['ACTIVE'] == 'ALL'){
            unset($filter['ACTIVE']);
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'PROPERTY_TEST' => false], $filter);
        } else {
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'ACTIVE' => 'Y', 'ACTIVE_DATE' => "Y", 'PROPERTY_TEST' => false], $filter);
        }

        global $USER;
        if($USER->GetID()==2){
            unset($arFilter['PROPERTY_TEST']);
        }
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], $select));
        $list = [];
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function setExpiredPeriod($course_id, $period)
    {
        self::updateCourseField($course_id, 'CERT_EXP', $period);
    }
    private static function updateCourseField($id, $code, $value)
    {
        IBlockHelper::includeIBlockModule();
        CIBlockElement::SetPropertyValuesEx($id, false, array($code => $value));
    }
}
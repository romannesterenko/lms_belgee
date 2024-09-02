<?php
namespace Teaching;
use CIBlockElement,
    CModule,
    Helpers\IBlockHelper,
    Helpers\PropertyHelper as Props,
    Helpers\UserHelper;
use Helpers\PropertyHelper;
use Models\Certificate;
use Models\Course;
use Models\Dealer;
use Models\Role;
use Models\User;
use Settings\Common;

class Courses
{
    public static function getList($filter, $select)
    {
        CModule::IncludeModule('iblock');

        $list = [];
        if($filter['ACTIVE'] == 'N'){
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'PROPERTY_TEST' => false], $filter);
        } elseif($filter['ACTIVE'] == 'ALL'){
            unset($filter['ACTIVE']);
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'PROPERTY_TEST' => false], $filter);
        } else {
            $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCoursesIBlock(), 'ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y', 'PROPERTY_TEST' => false], $filter);
        }
        global $USER;
        if ($USER->isAdmin()) {
            unset($arFilter['PROPERTY_TEST']);
        }
        $select = array_merge(['ID'], $select);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }
    public static function getCompletedCourses($user_id=0){
        $completions = new CourseCompletion();
        return $completions->getCompletedItems($user_id)->getArray();
    }
    public static function isPaid($course_id)
    {
        $course_price = (int)Props::getPropertyValue(self::getIblockId(), $course_id, 'COST');
        return $course_price>0;
    }
    public static function getCountOfCompetedCourses($user_id=0)
    {
        return count(self::getCompletedCourses($user_id));
    }
    public static function isFreeSheduleCourse($ID)
    {
        IBlockHelper::includeIBlockModule();
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getCoursesIBlock(), $ID, array("sort" => "asc"), array("CODE" => "COURSE_TYPE"));
        if ($prop = $db_props->Fetch()) {
            return (int)$prop['VALUE'] == 6;
        }
        return false;
    }
    public static function isHybridCourse($ID)
    {
        IBlockHelper::includeIBlockModule();
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getCoursesIBlock(), $ID, array("sort" => "asc"), array("CODE" => "COURSE_TYPE"));
        if ($prop = $db_props->Fetch()) {
            dump($prop);
            return (int)$prop['VALUE'] == 125;
        }
        return false;
    }
    public static function isNeedScheduleCourse($ID)
    {
        IBlockHelper::includeIBlockModule();
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getCoursesIBlock(), $ID, array("sort" => "asc"), array("CODE" => "COURSE_TYPE"));
        if ($prop = $db_props->Fetch()) {
            return (int)$prop['VALUE'] == 5;
        }
        return false;
    }

    public static function getNeededCoursesIds($user_id = 0)
    {
        $enrollments = new Enrollments();
        $ids = Roles::GetRequiredCourseIdsByUser();
        $exist_ids = [];
        foreach ($enrollments->getListByUser(UserHelper::prepareUserId($user_id), false) as $enroll) {
            $exist_ids[] = $enroll['UF_COURSE_ID'];
        }
        return count($exist_ids) > 0 ? array_values(array_diff($ids, $exist_ids)) : $ids;
    }

    public static function getByRole($role_id, $select)
    {
        $not_must =  self::getList(['PROPERTY_ROLES' => $role_id], $select);
        $must_ids = current(Role::getList(['ID' => $role_id], ['ID', 'PROPERTY_COURSES']));
        $must_courses = [];
        if(check_full_array($must_ids)&&check_full_array($must_ids['PROPERTY_COURSES_VALUE'])){
            $must_courses = self::getList(['ID' => $must_ids['PROPERTY_COURSES_VALUE']], $select);
        }
        foreach ($must_courses as $course_id => $course){
            if(!check_full_array($not_must[$course_id]))
                $not_must[$course_id] = $course;
        }
        return $not_must;
    }

    public static function getNeededByRoles($role_id, $select)
    {
        if(is_array($role_id)){
            $must_ids['PROPERTY_COURSES_VALUE'] = [];
            $roles = Role::getList(['ID' => $role_id], ['ID', 'PROPERTY_COURSES']);
            foreach ($roles as $role){
                $must_ids['PROPERTY_COURSES_VALUE'] = array_merge($must_ids['PROPERTY_COURSES_VALUE'], $role['PROPERTY_COURSES_VALUE']);
            }
        } else {
            $must_ids = current(Role::getList(['ID' => $role_id], ['ID', 'PROPERTY_COURSES']));
        }
        $must_courses = [];
        if(check_full_array($must_ids)&&check_full_array($must_ids['PROPERTY_COURSES_VALUE'])){
            $must_courses = self::getList(['ID' => $must_ids['PROPERTY_COURSES_VALUE']], $select);
        }
        return $must_courses;
    }

    public static function getByRoleArray($role_array, $select)
    {
        $return_array = [];
        $select[] = 'PROPERTY_ROLES';
        $list = self::getList(['PROPERTY_ROLES' => $role_array], $select);
        foreach ($list as $it)
            $return_array[$it['ID']] = $it;
        return $list;
    }

    public static function getByCode($code, $select)
    {
        $items = self::getList(['CODE' => $code], $select);
        return check_full_array($items)?current($items):[];
    }

    public static function getIdsByRole($role_id)
    {
        //\Helpers\Log::write($role_id);
        if(is_null($role_id)||count($role_id)==0)
            return [];
        return array_keys(self::getList(['PROPERTY_ROLES' => $role_id], ['ID']));
    }

    public static function getIdsByRoleOfCurrentUser()
    {
        return self::getIdsByRole(\Helpers\UserHelper::getRoleByCurrentUser());
    }

    public static function getById($COURSE_ID, $not_check_active = false)
    {
        $filter = ['ID' => $COURSE_ID];
        if($not_check_active){
            $filter['ACTIVE'] = "ALL";
        }
        $list = current(self::getList($filter, ['IBLOCK_ID', 'ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'CODE']));
        if ($list['ID']>0)
            return \Helpers\PropertyHelper::collectFields($list);
        return [];
    }

    public static function generateUrl($CODE)
    {
        return '/courses/' . $CODE . '/';
    }

    public static function getNewCoursesIds()
    {
        return Courses::getIdsByRole(UserHelper::getRoleByCurrentUser());
    }

    public static function getCountOfNeededCourses($ID)
    {
        $enrollments = new \Teaching\Enrollments();
        $ids = \Teaching\Roles::GetRequiredCourseIdsByUser($ID);
        $exist_ids = [];
        foreach ($enrollments->getListByUser($ID, false) as $enroll) {
            $exist_ids[] = $enroll['UF_COURSE_ID'];
        }
        $needed_courses_filter = count($exist_ids) > 0 ? array_diff($ids, $exist_ids) : $ids;
        return count($needed_courses_filter);
    }

    public static function getCoursesByUser($user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $required = self::getNeededCoursesIds($user_id);
        $not_required = self::getIdsByRole(UserHelper::getRoleByUser($user_id));
        $by_user = UserHelper::getUserValue($user_id, 'UF_REQUIRED_COURSES');
        $by_user = check_full_array($by_user)?$by_user:[];
        return array_values(
                array_unique(
                    array_merge(
                        $required, $not_required, $by_user
                    )
                )
            );
    }
    public static function wasStarted($id)
    {
        $completions = new \Teaching\CourseCompletion();
        return $completions->wasStarted($id);
    }

    /*public static function wasEnrolled($id)
    {
        $completions = new \Teaching\CourseCompletion();
        return $completions->wasStarted($id);
    }*/

    public static function hasRightsToCompletion($course_id)
    {
        global $USER;
        $course = \Models\Course::find($course_id, ['ID', 'ACTIVE']);
        if(!check_full_array($course)||$course['ACTIVE']!='Y')
            return false;

        $completions = new \Teaching\CourseCompletion();
        $completion = $completions->getByCourseAndUser($USER->GetID(), $course_id);
        if(!check_full_array($completion))
            return false;
        return true;
    }

    public static function isCompleted($ID, $user_id=0) {
        global $USER;
        $completions = new \Teaching\CourseCompletion();
        $user_id = UserHelper::prepareUserId($user_id);
        /*if($USER->GetID()==2)
            return self::isCompleted1($ID);*/
        return $completions->isCompleted($ID, $user_id);
    }

    public static function isCompleted1($ID, $user_id=0) {
        $completions = new \Teaching\CourseCompletion();
        return $completions->isCompleted1($ID, $user_id);
    }

    private static function getIblockId()
    {
        return IBlockHelper::getCoursesIBlock();
    }

    public static function checkCoupon($course_id, $coupon, $user_id)
    {
        if(empty($coupon))
            return false;
        $exists = Certificate::getByCodeAndCourseNotActivated($coupon, $course_id);
        if($exists['ID']>0) {
            Certificate::activate($exists['ID'], $user_id);
            return true;
        }
        return false;
    }

    public static function getZoomType($COURSE)
    {
        return PropertyHelper::getPropertyValue(self::getIblockId(), $COURSE, 'ZOOM');
    }

    public static function isForUser($ID)
    {
        return true;
        $user_id = UserHelper::prepareUserId(0);
        $by_user = UserHelper::getUserValue($user_id, 'UF_REQUIRED_COURSES');
        $by_user = is_array($by_user)?$by_user:[];
        $by_role = \Teaching\Roles::GetRequiredCourseIdsByRole(UserHelper::getRoleByUser($user_id));
        $merge_ids = array_unique(array_merge($by_user, $by_role));
        $ids = [];
        if(count($merge_ids)>0) {
            foreach (Courses::getList(['ACTIVE' => 'Y', 'ID' => $merge_ids], ['ID']) as $item)
                $ids[] = $item['ID'];
        }
        return in_array($ID, $ids);
    }

    public static function getByScheduleId($int, $select = [])
    {
        if(check_full_array($select)){
            return Course::find(\Teaching\SheduleCourses::getCourseIdBySchedule($int), $select);
        } else {
            return \Teaching\Courses::getById(\Teaching\SheduleCourses::getCourseIdBySchedule($int));
        }
    }

    public static function getIdByCompletion($ID)
    {
        return \Teaching\Completion::getCourseID($ID);
    }

    public static function getDirectionsList()
    {
        $directions = new \Teaching\CourseCategory();
        return $directions->getAll();
    }

    public static function getByDirection($value)
    {
        return self::getList(['PROPERTY_COURSE_CATEGORY' => $value], ['ID', 'NAME', 'PROPERTY_COURSE_CATEGORY']);
    }

    public static function getDirectionsByName($dir)
    {
        $directions = new \Teaching\CourseCategory();
        return $directions->getByName($dir);
    }

    public static function getByTrainer($id)
    {
        return self::getList(['PROPERTY_TRAINERS' => $id], ['ID', 'NAME']);
    }

    public static function getIdsByTrainer($id)
    {
        $ids = [];
//        foreach(self::getList(['PROPERTY_TRAINERS' => $id], ['ID', 'NAME']) as $item)
        foreach(self::getList([], ['ID', 'NAME']) as $item)
            $ids[] = $item['ID'];
        return $ids;
    }

    public static function getCoursesBefore($ID)
    {
        if((int)$ID > 0){
            $c = current(self::getList(['ID' => $ID], ['ID', 'NAME', 'PROPERTY_NEED_COURSES_BEFORE']));
            if(check_full_array($c)&&check_full_array($c['PROPERTY_NEED_COURSES_BEFORE_VALUE'])) {
                //dump($c['PROPERTY_NEED_COURSES_BEFORE_VALUE']);
                return $c['PROPERTY_NEED_COURSES_BEFORE_VALUE'];
            }else{
                return [];
            }

        } else {
            return [];
        }
    }

    public static function getAllCoursesForUser($ID)
    {
    }

    public static function isExpired($int, $user_id = 0) {
        $completions = new \Teaching\CourseCompletion();
        return $completions->isExpired($int, $user_id);
    }

    public static function isRetestFailed($int, $user_id = 0): bool
    {
        if(!Course::hasRetest($int))
            return false;
        $user_id = UserHelper::prepareUserId($user_id);
        $completion = current((new \Teaching\CourseCompletion())->get(
            ['UF_COURSE_ID' => $int, 'UF_USER_ID' => $user_id]
        ));
        if(check_full_array($completion))
            return $completion['UF_IS_COMPLETE'] == 1 && $completion['UF_RETEST_FAILED'] == 1;
        return false;
    }

    public static function isNeedRetest($int) {
        return PropertyHelper::getPropertyValue(self::getIblockId(), $int, 'HAS_RETEST') == 155;
    }

    public static function isAllowToEnrollByBalance($course_id, $user_id = 0): bool
    {

        if(Common::isAllowToEnrollMinusBalance())
            return true;
        if(!Course::isBalancePayment($course_id))
            return true;
        $user_id = UserHelper::prepareUserId($user_id);
        if(Course::isPaid($course_id)){
            $dealer = UserHelper::getDealerId($user_id);
            if(Common::isTestingBalanceMode() && $dealer!=Common::getTestingBalanceDealer()){
                return true;
            }
            $direction = Course::getCourseCategory($course_id);
            $sum = Course::getCost($course_id);
            $balance = Dealer::getAllBalance($dealer);
            if (check_full_array($balance) && $sum > 0 && $balance[$direction]['free'] < $sum)
                return false;

        }
        return true;
    }
    public static function isAllowToEnrollByCourseAndBalance($course_id, $user_id = 0): bool
    {

        if(Common::isAllowToEnrollMinusBalance())
            return true;
        $user_id = UserHelper::prepareUserId($user_id);
        if(Course::isPaid($course_id)){
            $dealer = UserHelper::getDealerId($user_id);
            if(Common::isTestingBalanceMode() && $dealer!=Common::getTestingBalanceDealer()){
                return true;
            }
            $direction = Course::getCourseCategory($course_id);

            $sum = Course::getCost($course_id);
            $balance = Dealer::getAllBalance($dealer);
            if (check_full_array($balance) && $sum > 0 && $balance[$direction]['free'] < $sum)
                return false;

        }
        return true;
    }
    public static function isAllowToEnrollByCourseAndDealer($course_id, $dealer = 0): bool
    {

        if(Common::isAllowToEnrollMinusBalance())
            return true;
        if(Course::isPaid($course_id)){
            if($dealer == 0){
                $user_id = UserHelper::prepareUserId(0);
                $dealer = UserHelper::getDealerId($user_id);
            }

            if(Common::isTestingBalanceMode() && $dealer!=Common::getTestingBalanceDealer()){
                return true;
            }
            $direction = Course::getCourseCategory($course_id);

            $sum = Course::getCost($course_id);
            $balance = Dealer::getAllBalance($dealer);
            if (check_full_array($balance) && $sum > 0 && $balance[$direction]['free'] < $sum)
                return false;

        }
        return true;
    }

    public static function getOpList(): array
    {
        $return_ids = [];
        $list = self::getList(['SECTION_ID' => 4, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID']);
        foreach ($list as $item) {
            $return_ids[] = $item['ID'];
        }
        return $return_ids;
    }

    public static function getPpoList(): array
    {
        $return_ids = [];
        $list = self::getList(['SECTION_ID' => 17, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID']);
        foreach ($list as $item) {
            $return_ids[] = $item['ID'];
        }
        return $return_ids;
    }

    public static function getTestList()
    {
        return self::getList(['PROPERTY_TEST' => 118], ['ID', 'NAME']);
    }

    public static function isAllowToEnrollByCountry($course_id, $user_id = 0): bool
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $dealer = UserHelper::getDealerId($user_id);
        $countries = Course::getDenieCountries($course_id);
        $dealer_country = Dealer::getCountry($dealer);
        if(!$dealer_country)
            return true;
        if(!check_full_array($countries))
            return true;
        return !in_array($dealer_country, $countries);
    }
}
<?php
namespace Settings;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CIBlockElement;
use CIBlockSection;
use CUser;
use CUserFieldEnum;
use Helpers\DealerHelper;
use Helpers\IBlockHelper;
use Helpers\PropertyHelper;
use Models\Course;
use Models\Dealer;
use Models\Employee;
use Models\Role;
use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\Roles;
use Teaching\SheduleCourses;
use Teaching\Tests;

class Reports
{
    private static $user_entity_id = 399;
    private static $course_entity_id = 401;
    private static $dealer_entity_id = 400;
    public static function getIblockID(){
        return 15;
    }
    public static function arrayEntities(){
        return [
            29 => 'USER',
            30 => 'COURSE',
            31 => 'POLL',
            32 => 'DEALER',
        ];
    }
    public static function getEmployeesForCourse($course_id){
        if(Courses::isFreeSheduleCourse($course_id)){
            $completions = new CourseCompletion();
            $items = $completions->getItemsByCourseID($course_id);
        }else{
            $enrollments = new Enrollments();
            $items = $enrollments->getAllByCourseId($course_id);
        }
        $report_template = self::getCourseCompletionsTemplate();
        $new_items = [];
        foreach ($items as $item){
            $new_item = [];
            if($item['UF_USER_ID']>0&&!empty($report_template['PROPERTY_USER_VALUE'])){
                foreach (User::find($item['UF_USER_ID'], self::parseFields($report_template['PROPERTY_USER_VALUE'])) as $name=>$value){
                    $new_item['USER.'.$name] = $value;
                }
                unset($item['UF_USER_ID']);
            }
            if($item['UF_COURSE_ID']>0&&!empty($report_template['PROPERTY_COURSE_VALUE'])){
                foreach (current(Courses::getList(['ID' => $item['UF_COURSE_ID']], self::parseFields($report_template['PROPERTY_COURSE_VALUE']))) as $name => $value) {
                    if(strpos($name, '~')===false)
                        $new_item['COURSE.' . $name] = $value;
                }
                unset($item['UF_COURSE_ID']);
            }
            if($item['UF_SHEDULE_ID']>0&&!empty($report_template['PROPERTY_SCHEDULE_VALUE'])){
                foreach (current(SheduleCourses::getArray(['ID' => $item['UF_SHEDULE_ID']], self::parseFields($report_template['PROPERTY_SCHEDULE_VALUE']))) as $name => $value) {
                    if(strpos($name, '~')===false)
                        $new_item['SCHEDULE.' . $name] = $value;
                }
                unset($item['UF_SHEDULE_ID']);
            }
            unset($item['UF_DATE_UPDATE']);
            unset($item['ID']);
            $item['UF_IS_COMPLETE']=$item['UF_IS_COMPLETE']==1?'Да':"Нет";
            $item['UF_IS_APPROVED']=$item['UF_IS_APPROVED']==1?'Да':"Нет";
            $new_items[] = array_merge($new_item, $item);
        }
        return $new_items;
    }
    public static function parseFields($string){
        return array_merge(['ID'], explode(';', $string));
    }

    public static function get($filter = [], $select = []){
        $sections = self::arrayEntities();
        IBlockHelper::includeIBlockModule();
        $arSelect = count($select)>0?$select:Array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID");
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], $filter);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while($ob = $res->fetch()){
            $report = $ob;
            $report = PropertyHelper::collectFields($report);
            $return_array = self::getMainEntityGetList($sections[$report['IBLOCK_SECTION_ID']], $report['PROPERTIES']);
            foreach ($return_array as &$item){
                foreach ($report['PROPERTIES'] as $name=>$value){
                    if($sections[$report['IBLOCK_SECTION_ID']]==$name||empty($value))
                        continue;
                    $item[$name] = self::getAdditionalEntity($item, $name, $value);
                }
                dump($item);
            }
        }
    }

    private static function getCourseCompletionsTemplate()
    {
        IBlockHelper::includeIBlockModule();
        $arSelect = ["ID", "IBLOCK_ID", "PROPERTY_COURSE", "PROPERTY_USER", "PROPERTY_DEALER", "PROPERTY_SCHEDULE"];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['PROPERTY_FOR_ENTITY' => self::$course_entity_id, 'PROPERTY_ENTITY' => self::$user_entity_id]);
        return CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch();
    }

    public static function getEmployees()
    {
        $report_template = self::getEmployeesListTemplate();
        $getListParams['filter'] = ['>ID'=>0];
        $getListParams['select'] = self::parseFields($report_template['PROPERTY_USER_VALUE']);
        $items = UserTable::getList($getListParams)->fetchAll();
        $new_items = [];
        foreach ($items as $item){
            if(!empty($report_template['PROPERTY_ENROLLS_VALUE'])){
                $global_name = 'ENROLLS.';
                $enrollments = new Enrollments();
                $fields_arr = explode(';', $report_template['PROPERTY_ENROLLS_VALUE']);
                if(in_array('APPROVED_CNT', $fields_arr))
                    $item[$global_name.'APPROVED_CNT'] = count($enrollments->getApprovedEnrolls($item['ID']));
                if(in_array('NOT_APPROVED_CNT', $fields_arr))
                    $item[$global_name.'NOT_APPROVED_CNT'] = count($enrollments->getNotApprovedEnrolls($item['ID']));

            }
            if(!empty($report_template['PROPERTY_COMPLETIONS_VALUE'])){
                $global_name = 'COMPLETIONS.';
                $enrollments = new CourseCompletion();
                $fields_arr = explode(';', $report_template['PROPERTY_COMPLETIONS_VALUE']);
                if(in_array('COMPLETE_CNT', $fields_arr))
                    $item[$global_name.'COMPLETE_CNT'] = $enrollments->getCompletedItems($item['ID'])->getCount();
                if(in_array('IN_PROCESS_CNT', $fields_arr))
                    $item[$global_name.'IN_PROCESS_CNT'] = count($enrollments->getAllStartedCompletionsByUser($item['ID']));

            }
            $new_items[] = $item;
        }
        return $new_items;
    }

    private static function getEmployeesListTemplate()
    {
        IBlockHelper::includeIBlockModule();
        $arSelect = [
            "ID",
            "IBLOCK_ID",
            "PROPERTY_COURSE",
            "PROPERTY_USER",
            "PROPERTY_DEALER",
            "PROPERTY_ENROLLS",
            "PROPERTY_COMPLETIONS",
            "PROPERTY_SCHEDULE"
        ];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['PROPERTY_FOR_ENTITY' => false, 'PROPERTY_ENTITY' => self::$user_entity_id]);
        return CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch();
    }

    private static function getCoursesTemplateSetting()
    {
        IBlockHelper::includeIBlockModule();
        $arSelect = [
            "ID",
            "IBLOCK_ID",
        ];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['PROPERTY_FOR_ENTITY' => false, 'PROPERTY_ENTITY' => self::$course_entity_id]);
        return PropertyHelper::collectFields(CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch());
    }

    private static function getDealersTemplateSetting()
    {
        IBlockHelper::includeIBlockModule();
        $arSelect = [
            "ID",
            "IBLOCK_ID",
        ];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['PROPERTY_FOR_ENTITY' => false, 'PROPERTY_ENTITY' => self::$dealer_entity_id]);
        return PropertyHelper::collectFields(CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch());
    }
    private static function getDealersListTemplate()
    {
        IBlockHelper::includeIBlockModule();
        $arSelect = [
            "ID",
            "IBLOCK_ID",
            "PROPERTY_COURSE",
            "PROPERTY_USER",
            "PROPERTY_DEALER",
            "PROPERTY_ENROLLS",
            "PROPERTY_COMPLETIONS",
            "PROPERTY_SCHEDULE",
            "PROPERTY_IS_ADAPTIVE"
        ];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['PROPERTY_FOR_ENTITY' => false, 'PROPERTY_ENTITY' => self::$dealer_entity_id]);
        return CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch();
    }

    public static function getDealers()
    {
        $new_items = [];
        $report_template = self::getDealersListTemplate();
        $list = DealerHelper::getList(['ACTIVE'=>'Y'], self::parseFields($report_template['PROPERTY_DEALER_VALUE']));
        foreach ($list as $dealer){
            foreach ($dealer as $key => $value) {
                if (strpos($key, '~') !== false)
                    unset($dealer[$key]);
            }
            if(!empty($report_template['PROPERTY_USER_VALUE'])){
                $global_name = 'USERS.';
                $fields_arr = explode(';', $report_template['PROPERTY_USER_VALUE']);
                if(in_array('COUNT', $fields_arr))
                    $dealer[$global_name.'COUNT'] = count(Employee::getByDealer($dealer['ID']));
                if(in_array('ATTESTATED_COUNT', $fields_arr))
                    $dealer[$global_name.'ATTESTATED_COUNT'] = count(Employee::getAttestatedByDealer($dealer['ID']));
                if(in_array('NOT_ATTESTATED_COUNT', $fields_arr))
                    $dealer[$global_name.'NOT_ATTESTATED_COUNT'] = count(Employee::getNotAttestatedByDealer($dealer['ID']));
            }

            $new_items[] = $dealer;
        }
        return $new_items;
    }

    public static function getDirections()
    {
        return Courses::getDirectionsList();
    }

    public static function getDealerReport()
    {
        return self::getDealersTemplateSetting();
    }

    public static function getCoursesReport()
    {
        return self::getCoursesTemplateSetting();
    }
    public static function getByCode($code){
        IBlockHelper::includeIBlockModule();
        $arSelect = [
            "ID",
            "IBLOCK_ID",
        ];
        $arFilter = array_merge(['IBLOCK_ID' => self::getIblockID()], ['CODE' => $code]);
        return PropertyHelper::collectFields(CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect)->Fetch());
    }
    public static function getCoursesDealerReport()
    {
        $settings = Reports::getByCode('courses_for_dealer');
        $dealer = current(DealerHelper::getList(['ID'=> Employee::getDealerId()], ['ID', 'NAME', 'PROPERTY_CITY']));
        $dealer['SETTINGS'] = $settings;
        $employees = Employee::getListByDealer($dealer['ID'], ['ID', 'UF_ROLE']);
        $roles = [];
        $completions = new CourseCompletion();
        foreach($employees as $employee) {
            if(is_array($employee['UF_ROLE']))
                $roles = array_merge($roles, $employee['UF_ROLE']);
        }
        $dealer['ROLES_LIST'] = Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);
        $courses = Courses::getByRole(array_keys($dealer['ROLES_LIST']), ['ID', 'NAME']);
        foreach ($dealer['ROLES_LIST'] as &$role){
            $role['COURSES'] = Courses::getByRole($role['ID'], ['ID', 'NAME']);
            foreach ($employees as $employee) {
                if (in_array($role['ID'], $employee['UF_ROLE']))
                    $role['USERS'][] = $employee;
            }
            foreach ($role['COURSES'] as &$course){
                $course['COMPLETED'] = 0;
                foreach ($role['USERS'] as $user){
                    if($completions->isCompleted($course['ID'], $user['ID']))
                        $course['COMPLETED']++;
                }
                $course['COMPLETED_PERCENTS'] = $course['COMPLETED']==0?'0%':floor($course['COMPLETED']/count($role['USERS'])*100).'%';
            }
        }
        $dealer['EMPLOYESS_CNT'] = count($employees);
        return $dealer;
    }
    public static function getHalfYearReport()
    {
        return self::getByCode('half_year_report');
    }
    public static function getRolesForDealerReport()
    {
        return self::getByCode('roles_for_dealer');
    }

    public static function getCompletionsReport()
    {
        return self::getByCode('examens');
    }

    public static function getMenDaysPerfReport()
    {
        return self::getByCode('days_prtfomance');
    }

    private static function getSectionList($filter, $select)
    {
        $dbSection = CIBlockSection::GetList(
            Array(
                'LEFT_MARGIN' => 'ASC',
            ),
            array_merge(
                Array(
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y'
                ),
                is_array($filter) ? $filter : Array()
            ),
            false,
            array_merge(
                Array(
                    'ID',
                    'IBLOCK_SECTION_ID'
                ),
                is_array($select) ? $select : Array()
            )
        );

        while( $arSection = $dbSection-> GetNext(true, false) ){

            $SID = $arSection['ID'];
            $PSID = (int) $arSection['IBLOCK_SECTION_ID'];

            $arLincs[$PSID]['CHILDS'][$SID] = $arSection;

            $arLincs[$SID] = &$arLincs[$PSID]['CHILDS'][$SID];
        }

        return array_shift($arLincs);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function generateTest($for_dealer = false, $withoutGmr = false)
    {
        unset($_REQUEST['city']);
        if($for_dealer && !check_full_array($_REQUEST['dealer_codes']))
            return [];
        $need_setted_courses = true;
        $role_ids = [];
        //первичный фильтр для пользователей
        $user_filter['ACTIVE'] = 'Y';
        $user_filter['!UF_DEALER'] = false;
        //установка ролей в зависимости от выбранного направления
        $roles = [];
        switch ($_REQUEST['direction']) {
            case 'S01':
                $roles = Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
                break;
            case 'A01':
                $roles = Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
                break;
            case 'M01':
                $roles = Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
                break;
            case 'all':
                $roles = Role::getAll(['ID', 'NAME']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
                break;
        }
        //если ролей нет останавливаемся
        if(!check_full_array($roles))
            return [];
        $withoutDealers = [];
        if($withoutGmr)
            $withoutDealers = [360, 292];
        if ($_REQUEST['direction'] != 'all') {
            $need_setted_courses = false;
        }
        //если установлен ППО регионал, добавляем его дилеров в фильтр пользователей
        if(check_full_array($_REQUEST['regional_ppo'])) {
            $dealers = Dealer::getByRegionalPPO($_REQUEST['regional_ppo'], $withoutDealers);
            $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
        }

        //если установлен ОП регионал, добавляем его дилеров в фильтр пользователей
        if(check_full_array($_REQUEST['regional_op'])) {
            $dealers = Dealer::getByRegionalOP($_REQUEST['regional_op'], ['ID', 'NAME', 'CODE'], $withoutDealers);
            $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
        }

        //если установлен ОП регионал, добавляем его дилеров в фильтр пользователей
        if(check_full_array($_REQUEST['regional_marketing'])) {
            $dealers = Dealer::getByRegionalMarketing($_REQUEST['regional_marketing'], ['ID', 'NAME', 'CODE'], $withoutDealers);
            $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
        }

        //если установлена страна, добавляем дилеров только этой страны в фильтр пользователей
        if(!empty($_REQUEST['country'])){
            $dealers = Dealer::getList(['!ID' => $withoutDealers, 'ACTIVE' => 'Y', 'PROPERTY_COUNTRY' => $_REQUEST['country']]);
            $user_filter['UF_DEALER'] = check_full_array((array)$user_filter['UF_DEALER'])?array_intersect($user_filter['UF_DEALER'], array_keys($dealers)):array_keys($dealers);
        }

        //если установлена страна, добавляем дилеров только этой страны в фильтр пользователей
        if(check_full_array($_REQUEST['dealer_codes'])) {
            $user_filter['UF_DEALER'] = $_REQUEST['dealer_codes'];
        }

        //если установлены роли на прямую, добавляем их в фильтр пользователей
        if(check_full_array($_REQUEST['role'])) {
            $user_filter['UF_ROLE'] = $_REQUEST['role'];
            $need_setted_courses = false;
        }
        $city_courses = [];
        $city_shedules = [];
        if ($_REQUEST['city'] && $_REQUEST['city']!='all'){
            $city_courses = Course::getByCity($_REQUEST['city'], true);
            $city_shedules = SheduleCourses::getByCity($_REQUEST['city'], true);
            //dump($city_shedules);
        }
        //dump($city_courses);
        dump($city_shedules);
        //если установлены курсы, получаем пользователей
        if (check_full_array($_REQUEST['courses'])) {
            if (check_full_array($city_shedules)){

                //$city_shedule_courses = Course::getList()
            }
            if (check_full_array($city_courses))
                $_REQUEST['courses'] = array_intersect($city_courses, $_REQUEST['courses']);
            $need_and_filter = $_REQUEST['need_and']=='on'&&count($_REQUEST['courses'])>1;
            $ids = [];
            if($need_and_filter)
                $user_ids_temp = [];
            //перебираем все курсы из $_REQUEST
            foreach ($_REQUEST['courses'] as $c_id) {

                //Получаем все роли по курсу
                $cs = User::getEmployeesByRoleToCourse($c_id, true);
                if (check_full_array($cs)) {
                    if($need_and_filter)
                        $user_ids_temp[$c_id] = $cs;
                    $ids = array_merge($ids, $cs);
                }
                $setted = User::getBySettedCourse($c_id, true);
                if (check_full_array($setted)){
                    if($need_and_filter)
                        $user_ids_temp[$c_id] = array_values(array_unique(array_merge($user_ids_temp[$c_id], $setted)));
                    $ids = array_merge($ids, $setted);
                }
            }
            if($need_and_filter) {
                $commonValues = [];
                $firstArray = reset($user_ids_temp);
                foreach ($firstArray as $value) {
                    $isCommon = true;
                    foreach ($user_ids_temp as $key => $array) {
                        if ($key === key($user_ids_temp)) {
                            continue;
                        }
                        if (!in_array($value, $array)) {
                            $isCommon = false;
                            break;
                        }
                    }
                    if ($isCommon) {
                        $commonValues[] = $value;
                    }
                }
                if(check_full_array($commonValues)){
                    $ids = $commonValues;
                }
            }
            if(check_full_array($ids))
                $user_filter["ID"] = implode(' | ', $ids);
        }
        //если установлены пользователи напрямую, обнуляем все значения фильтра и добавляем их в фильтр пользователей
        if (check_full_array($_REQUEST['fio'])){
            unset($user_filter);
            $user_filter["ID"] = implode(' | ', $_REQUEST['fio']);
            $need_setted_courses = true;
        }
        //получаем список пользователей
        $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $user_filter, ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_MOBILE', 'WORK_POSITION'], 'SELECT' => [ 'UF_ROLE','UF_WORK_START_DATE', 'UF_DEALER', 'UF_REQUIRED_COURSES', "UF_USER_RATING"]]); // выбираем пользователей
        $users = [];
        while($arUser = $rsUsers->Fetch()){
            $dealer_ids[] = $arUser['UF_DEALER'];
            if($arUser['UF_ROLE'])
                $role_ids = array_merge($role_ids, $arUser['UF_ROLE']);
            $arUser['MUST_COURSES'] = [];
            $users[$arUser['ID']] = $arUser;
        }
        if(!check_full_array($users))
            return [];
        $role_ids = array_values(array_unique($role_ids));
        //Получаем список дилеров
        $dealers = Dealer::getList(['ID' => $dealer_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
        //формируем фильтр на прохождения
        $filter_completions['UF_USER_ID'] = array_keys($users);
        $filter_completions['UF_COURSE_ID'] = [];
        foreach ($role_ids as $role_id__) {
            $courses_by_role = Course::getMustByRole($role_id__, true);
            foreach ($users as $id => $user) {
                if(in_array($role_id__, $user['UF_ROLE'])) {
                    $users[$id]['MUST_COURSES'] = array_values(array_unique(array_merge($courses_by_role, $user['MUST_COURSES'])));
                }
            }
            $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $courses_by_role));
            unset($courses_by_role);
        }

        switch ($_REQUEST['direction']) {
            case 'S01':
                $direction_courses = Course::getOPList(true);
                $filter_completions['UF_COURSE_ID'] = array_intersect($direction_courses, $filter_completions['UF_COURSE_ID']);

                break;
            case 'A01':
                $direction_courses = Course::getPPOList(true);
                $filter_completions['UF_COURSE_ID'] = array_intersect($direction_courses, $filter_completions['UF_COURSE_ID']);
                break;
            case 'M01':
                $direction_courses = Course::getMarketingList(true);
                $filter_completions['UF_COURSE_ID'] = array_intersect($direction_courses, $filter_completions['UF_COURSE_ID']);

                break;
        }



        if(check_full_array($_REQUEST['role'])) {

            $filter_completions['UF_COURSE_ID'] = Course::getMustByRole($_REQUEST['role'], true);
            foreach ($users as $key => $user) {
                $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
            }
        }

        if(!empty($_REQUEST['course_date_before']))
            $filter_completions['>UF_DATE'] = date('d.m.Y 00:00:00', strtotime($_REQUEST['course_date_before']));
        if(!empty($_REQUEST['course_date_after']))
            $filter_completions['<UF_DATE'] = date('d.m.Y 23:59:59', strtotime($_REQUEST['course_date_after']));
        if ($_REQUEST['city'] && $_REQUEST['city']!='all') {
            unset($filter_completions['UF_COURSE_ID']);
            $filter_completions['UF_SHEDULE_ID'] = $city_shedules;
            $filter_completions['UF_COURSE_ID'] = Course::getIdsBySheduleIds($city_shedules);
            //$filter_completions['UF_COURSE_ID'] = array_intersect($city_courses, $filter_completions['UF_COURSE_ID']);
        }
        dump($filter_completions);
        $all_completions = (new CourseCompletion())->get($filter_completions);
        if($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on') {
            if($_REQUEST['status_complete']=='on'&&$_REQUEST['status_not_complete']!='on')
                $filter_completions['UF_IS_COMPLETE'] = 1;
            if($_REQUEST['status_complete']!='on'&&$_REQUEST['status_not_complete']=='on')
                $filter_completions['UF_IS_COMPLETE'] = false;
        }

        if($need_setted_courses){
            $setted_courses_ids = [];
            $filter_completions['UF_COURSE_ID'] = check_full_array($filter_completions['UF_COURSE_ID'])?$filter_completions['UF_COURSE_ID']:[];
            foreach ($users as $key => $user){
                if(check_full_array($user['UF_REQUIRED_COURSES'])) {
                    $setted_courses_ids = array_unique(array_merge($setted_courses_ids, $user['UF_REQUIRED_COURSES']));
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $user['UF_REQUIRED_COURSES']));
                    $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
                }
                if(check_full_array($user['UF_ROLE'])) {
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], Course::getMustByRole($user['UF_ROLE'], true)));
                    $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
                }
            }
        }

        if(check_full_array($_REQUEST['courses'])){
            $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
            foreach ($users as $key => $user){
                $users[$key]['MUST_COURSES'] = $_REQUEST['courses'];
            }
        }
        dump($_REQUEST['city']);
        if ($_REQUEST['city'] && $_REQUEST['city']!='all') {
            unset($filter_completions['UF_COURSE_ID']);
            $filter_completions['UF_SHEDULE_ID'] = $city_shedules;
            $filter_completions['UF_COURSE_ID'] = Course::getIdsBySheduleIds($city_shedules);
            //$filter_completions['UF_COURSE_ID'] = array_intersect($city_courses, $filter_completions['UF_COURSE_ID']);
        }
        dump($filter_completions);
        if(check_full_array($filter_completions['UF_COURSE_ID']) || check_full_array($filter_completions['UF_SHEDULE_ID'])) {
            if ($need_and_filter && ($_REQUEST['status_complete'] == 'on' || $_REQUEST['status_not_complete'] == 'on')) {

                $need_completions = (new CourseCompletion())->getAndCourses($filter_completions);
                $new_completions = [];
                foreach ($need_completions as $compl_user_id => $array_completions) {
                    if (count($array_completions) < count($_REQUEST['courses'])) {
                        unset($users[$compl_user_id]);
                    } else {
                        $array_exists_courses = [];
                        foreach ($array_completions as $array_completion) {
                            if (!in_array($array_completion["UF_COURSE_ID"], $array_exists_courses)) {
                                $array_exists_courses[] = $array_completion["UF_COURSE_ID"];
                            }
                        }
                        if (count($array_exists_courses) != count($_REQUEST['courses'])) {
                            unset($users[$compl_user_id]);
                        } else {
                            $new_array_exists_courses = [];
                            foreach ($array_completions as $array_completion) {
                                if (!in_array($array_completion["UF_COURSE_ID"], $new_array_exists_courses)) {
                                    $new_completions[] = $array_completion;
                                    $new_array_exists_courses[] = $array_completion["UF_COURSE_ID"];
                                }
                            }
                        }
                    }
                }
                $completions = $new_completions;
            } else {
                $completions = (new CourseCompletion())->get($filter_completions);
                //dump($completions);
            }
        }
        $course_ids = [];
        if(check_full_array($filter_completions['UF_COURSE_ID'])) {
            $course_ids = $filter_completions['UF_COURSE_ID'];
        } else {
            foreach ($completions as $one_completion)
                $course_ids[] = $one_completion['UF_COURSE_ID'];
        }
        $courses = check_full_array($course_ids)?Course::getList(['ID' => $course_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_SCORM', 'PROPERTY_COURSE_TYPE', 'PROPERTY_COURSE_FORMAT']):[];
        foreach ($courses as $kk => &$c_temp){
            if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']!=5) {
                if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']==125) {
                    $c_temp['MAX_POINTS'] = Tests::getMaxPointsByCourse($c_temp['ID']);
                } else {
                    if(check_full_array($c_temp['PROPERTY_SCORM_VALUE']))
                        $c_temp['MAX_POINTS'] = 100;
                    else
                        $c_temp['MAX_POINTS'] = Tests::getMaxPointsByCourse($c_temp['ID']);
                }
            }
        }

        $all_user_array = [];
        foreach ($all_completions as $completion){
            if($need_and_filter&&($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on')) {
                if(in_array($completion['UF_USER_ID'], $users)&&!check_full_array($all_user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']])) {
                    $all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']] = $completion;
                }
            } else {
                if (!check_full_array($all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']])) {
                    $all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']] = $completion;
                }
            }
        }
        unset($completion);
        foreach($completions as &$completion){
            $completion['COMPLETION_ID'] = $completion['ID'];
            $completion['ID'] = $completion['UF_USER_ID'];
            $completion['NAME'] = $users[$completion['UF_USER_ID']]['NAME'];
            $completion['LAST_NAME'] = $users[$completion['UF_USER_ID']]['LAST_NAME'];
            $completion['EMAIL'] = $users[$completion['UF_USER_ID']]['EMAIL'];
            $completion['UF_WORK_START_DATE'] = $users[$completion['UF_USER_ID']]['UF_WORK_START_DATE'];

            $completion['UF_ROLE'] = $users[$completion['UF_USER_ID']]['UF_ROLE'];
            $value = false;
            if((int)$users[$completion['UF_USER_ID']]['UF_USER_RATING'] > 0){
                $array = CUserFieldEnum::GetList([], ["ID" => $users[$completion['UF_USER_ID']]['UF_USER_RATING']])->Fetch();
                $value = $array['VALUE'];
            }
            $completion['UF_USER_RATING'] = $value;
            $completion['PERSONAL_MOBILE'] = $users[$completion['UF_USER_ID']]['PERSONAL_MOBILE'];
            $completion['WORK_POSITION'] = $users[$completion['UF_USER_ID']]['WORK_POSITION'];
            $completion['DEALER'] = $dealers[$users[$completion['UF_USER_ID']]['UF_DEALER']];
            $completion['COURSE']['INFO'] = $courses[$completion['UF_COURSE_ID']];
            if(!check_full_array($user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']]))
                $all_user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
        }
        if(!$_REQUEST['status_complete']=='on'&&!$_REQUEST['status_not_complete']=='on') {
            foreach ($users as $user) {
                foreach ($courses as $course) {
                    if (!check_full_array($all_user_array[$user['ID'] . "_" . $course['ID']])) {
                        $completion['ID'] = $user['ID'];
                        $completion['NAME'] = $user['NAME'];
                        $completion['LAST_NAME'] = $user['LAST_NAME'];
                        $completion['EMAIL'] = $user['EMAIL'];
                        $completion['UF_WORK_START_DATE'] = $user['UF_WORK_START_DATE'];

                        $completion['UF_ROLE'] = $user['UF_ROLE'];
                        $completion['UF_USER_ID'] = $user['ID'];
                        $completion['UF_COURSE_ID'] = $course['ID'];
                        $completion['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
                        $value = false;
                        if((int)$user['UF_USER_RATING'] > 0) {
                            $array = CUserFieldEnum::GetList([], ["ID" => $user['UF_USER_RATING']])->Fetch();
                            $value = $array['VALUE'];
                        }
                        $completion['UF_USER_RATING'] = $value;
                        $completion['WORK_POSITION'] = $user['WORK_POSITION'];
                        $completion['DEALER'] = $dealers[$user['UF_DEALER']];
                        $completion['COURSE']['INFO'] = $courses[$course['ID']];
                        if(check_full_array($user['MUST_COURSES'])&&in_array($course['ID'], $user['MUST_COURSES'])) {
                            $completion['NOT_ENROLLED'] = 1;
                        } else {
                            $completion['NOT_NEEDED'] = 1;
                        }
                        $completion['UF_IS_COMPLETE'] = false;
                        $user_array[$user['ID'] . "_" . $course['ID']] = $completion;
                        unset($completion);
                    }
                }
            }
        }
        return $user_array;
    }
    public static function generate($for_dealer = false, $withoutGmr = false)
    {
        if($for_dealer && !check_full_array($_REQUEST['dealer_codes']))
            return [];
        $completions = new CourseCompletion();
        $enrollments = new Enrollments();
        $roles_po_servis = self::getSectionList(['IBLOCK_ID' => IBlockHelper::getRolesIBlock()], ['ID', 'NAME']);

        $need_setted_courses = true;
        foreach ($roles_po_servis['CHILDS'] as $key => $sect) {
            $qwerty[$sect['ID']] = $sect['NAME'];
            if(check_full_array($sect['CHILDS'])) {
                foreach ($sect['CHILDS'] as $child)
                    $qwerty[$child['ID']] = $sect['NAME'];
            }
        }

        $role_ids = [];
        $user_filter['ACTIVE'] = 'Y';
        $user_filter['!UF_DEALER'] = false;
        if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
            if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
                $title_sop = 'Отдел продаж';
                $roles = Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
            }
            if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
                $title_sop = 'Отдел послепродажного обслуживания';
                $roles = Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
            }
            $need_setted_courses = false;
        }
        $withoutDealers = [];
        if($withoutGmr){
            $withoutDealers = [360, 292];
        }

        if(check_full_array($_REQUEST['regional_ppo'])) {
            $dealers = Dealer::getByRegionalPPO($_REQUEST['regional_ppo'], $withoutDealers);
            $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
        }
        if(check_full_array($_REQUEST['regional_op'])) {
            $dealers = Dealer::getByRegionalOP($_REQUEST['regional_op'], ['ID', 'NAME', 'CODE'], $withoutDealers);
            $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
        }
        if(!empty($_REQUEST['country'])){
            $dealers = Dealer::getList(['!ID' => [360, 292], 'ACTIVE' => 'Y', 'PROPERTY_COUNTRY' => $_REQUEST['country']]);
            $user_filter['UF_DEALER'] = check_full_array((array)$user_filter['UF_DEALER'])?array_intersect($user_filter['UF_DEALER'], array_keys($dealers)):array_keys($dealers);

        }
        if(check_full_array($_REQUEST['dealer_codes'])) {
            $user_filter['UF_DEALER'] = $_REQUEST['dealer_codes'];
        }
        $title_sop = false;
        if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
            if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
                $title_sop = 'Отдел продаж';
                $roles = Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
            }
            if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
                $title_sop = 'Отдел послепродажного обслуживания';
                $roles = Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
                if(check_full_array($roles))
                    $user_filter['UF_ROLE'] = array_keys($roles);
            }
        }

        if(check_full_array($_REQUEST['role'])){
            $user_filter['UF_ROLE'] = $_REQUEST['role'];
            $need_setted_courses = false;
        }



        if (check_full_array($_REQUEST['courses'])) {
            $need_and_filter = $_REQUEST['need_and']=='on'&&count($_REQUEST['courses'])>1;
            $ids = [];
            if($need_and_filter)
                $user_ids_temp = [];
            foreach ($_REQUEST['courses'] as $c_id) {
                $cs = User::getEmployeesByRoleToCourse($c_id, true);
                if (check_full_array($cs)) {
                    if($need_and_filter)
                        $user_ids_temp[$c_id] = $cs;
                    $ids = array_merge($ids, $cs);
                }
                $setted = User::getBySettedCourse($c_id, true);
                if (check_full_array($setted)){
                    if($need_and_filter)
                        $user_ids_temp[$c_id] = array_values(array_unique(array_merge($user_ids_temp[$c_id], $setted)));
                    $ids = array_merge($ids, $setted);
                }
            }
            if($need_and_filter) {
                $commonValues = [];
                // Получаем первый вложенный массив для инициализации
                $firstArray = reset($user_ids_temp);
                // Перебираем значения первого массива
                foreach ($firstArray as $value) {
                    $isCommon = true;
                    foreach ($user_ids_temp as $key => $array) {
                        if ($key === key($user_ids_temp)) {
                            continue;
                        }
                        if (!in_array($value, $array)) {
                            $isCommon = false;
                            break;
                        }
                    }
                    if ($isCommon) {
                        $commonValues[] = $value;
                    }
                }
                if(check_full_array($commonValues)){
                    $ids = $commonValues;
                }
            }
            //dump($ids);
            if(check_full_array($ids))
                $user_filter["ID"] = implode(' | ', $ids);
        }

        if (check_full_array($_REQUEST['fio'])){
            unset($user_filter);
            $user_filter["ID"] = implode(' | ', $_REQUEST['fio']);
            $need_setted_courses = true;
        }

        $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $user_filter, ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_MOBILE', 'WORK_POSITION'], 'SELECT' => [ 'UF_ROLE','UF_WORK_START_DATE', 'UF_DEALER', 'UF_REQUIRED_COURSES', "UF_USER_RATING"]]); // выбираем пользователей
        $users = [];
        while($arUser = $rsUsers->Fetch()){
            $dealer_ids[] = $arUser['UF_DEALER'];
            if($arUser['UF_ROLE'])
                $role_ids = array_merge($role_ids, $arUser['UF_ROLE']);
            $users[$arUser['ID']] = $arUser;
        }

        $temp_users = $users;
        $roless = Role::getList(['ID' => array_unique($role_ids)], ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
        $dealers = Dealer::getList(['ID' => $dealer_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
        $filter_completions['UF_USER_ID'] = check_full_array($users)?array_keys($users):[];

        if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
            if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
                $filter_completions['UF_COURSE_ID'] = [];
                foreach ($role_ids as $role_id__) {
                    $courses_by_role = Course::getMustByRole($role_id__, true);
                    foreach ($users as $id => $user) {
                        if(in_array($role_id__, $user['UF_ROLE']))
                            $users[$id]['MUST_COURSES'] = $courses_by_role;
                    }
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $courses_by_role));
                }
            }
            if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
                $filter_completions['UF_COURSE_ID'] = [];
                foreach ($role_ids as $role_id__) {
                    $courses_by_role = Course::getMustByRole($role_id__, true);
                    foreach ($users as $id => $user) {
                        if(in_array($role_id__, $user['UF_ROLE'])) {
                            $users[$id]['MUST_COURSES'] = $courses_by_role;
                        }
                    }
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], Course::getMustByRole($role_id__, true)));
                }
            }
        }
        if(check_full_array($_REQUEST['role'])) {
            $filter_completions['UF_COURSE_ID'] = Course::getMustByRole($_REQUEST['role'], true);
            foreach ($users as $key => $user){
                $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
            }
        }

        if(!empty($_REQUEST['course_date_before']))
            $filter_completions['>UF_DATE'] = date('d.m.Y 00:00:00', strtotime($_REQUEST['course_date_before']));
        if(!empty($_REQUEST['course_date_after']))
            $filter_completions['<UF_DATE'] = date('d.m.Y 23:59:59', strtotime($_REQUEST['course_date_after']));
        $all_completions = (new CourseCompletion())->get($filter_completions);
        if($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on') {
            if($_REQUEST['status_complete']=='on'&&$_REQUEST['status_not_complete']!='on')
                $filter_completions['UF_IS_COMPLETE'] = 1;
            if($_REQUEST['status_complete']!='on'&&$_REQUEST['status_not_complete']=='on')
                $filter_completions['UF_IS_COMPLETE'] = false;
        }

        if($need_setted_courses){
            $setted_courses_ids = [];
            $filter_completions['UF_COURSE_ID'] = check_full_array($filter_completions['UF_COURSE_ID'])?$filter_completions['UF_COURSE_ID']:[];
            foreach ($users as $key => $user){
                if(check_full_array($user['UF_REQUIRED_COURSES'])) {
                    $setted_courses_ids = array_unique(array_merge($setted_courses_ids, $user['UF_REQUIRED_COURSES']));
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $user['UF_REQUIRED_COURSES']));
                    $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
                }
                if(check_full_array($user['UF_ROLE'])) {
                    $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], Course::getMustByRole($user['UF_ROLE'], true)));
                    $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
                }
            }
        }

        if(check_full_array($_REQUEST['courses'])){
            $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
            foreach ($users as $key => $user){
                $users[$key]['MUST_COURSES'] = $_REQUEST['courses'];
            }
        }
        if($need_and_filter && ($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on')) {

            $need_completions = (new CourseCompletion())->getAndCourses($filter_completions);
            $new_completions = [];
            foreach ($need_completions as $compl_user_id => $array_completions){
                if(count($array_completions)<count($_REQUEST['courses'])) {
                    unset($users[$compl_user_id]);
                } else {
                    $array_exists_courses = [];
                    foreach ($array_completions as $array_completion) {
                        if(!in_array($array_completion["UF_COURSE_ID"], $array_exists_courses)) {
                            $array_exists_courses[] = $array_completion["UF_COURSE_ID"];
                        }
                    }
                    if(count($array_exists_courses)!=count($_REQUEST['courses'])){
                        unset($users[$compl_user_id]);
                    } else {
                        $new_array_exists_courses = [];
                        foreach ($array_completions as $array_completion) {
                            if(!in_array($array_completion["UF_COURSE_ID"], $new_array_exists_courses)) {
                                $new_completions[] = $array_completion;
                                $new_array_exists_courses[] = $array_completion["UF_COURSE_ID"];
                            }
                        }
                    }
                }
            }
            $completions = $new_completions;
        } else {
            $completions = (new CourseCompletion())->get($filter_completions);
        }

        $course_ids = [];
        if(check_full_array($filter_completions['UF_COURSE_ID'])) {
            $course_ids = $filter_completions['UF_COURSE_ID'];
        } else {
            foreach ($completions as $one_completion)
                $course_ids[] = $one_completion['UF_COURSE_ID'];
        }

        $courses = Course::getList(['ID' => $course_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_SCORM', 'PROPERTY_COURSE_TYPE', 'PROPERTY_COURSE_FORMAT']);

        foreach ($courses as $kk => &$c_temp){
            if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']!=5) {
                if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']==125) {
                    $c_temp['MAX_POINTS'] = Tests::getMaxPointsByCourse($c_temp['ID']);
                } else {
                    if(check_full_array($c_temp['PROPERTY_SCORM_VALUE']))
                        $c_temp['MAX_POINTS'] = 100;
                    else
                        $c_temp['MAX_POINTS'] = Tests::getMaxPointsByCourse($c_temp['ID']);
                }
            }
        }

        $all_user_array = [];
        foreach ($all_completions as $completion){
            if($need_and_filter&&($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on')) {
                if(in_array($completion['UF_USER_ID'], $users)&&!check_full_array($all_user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']])) {
                    $all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']] = $completion;
                }
            } else {
                if (!check_full_array($all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']])) {
                    $all_user_array[$completion['UF_USER_ID'] . "_" . $completion['UF_COURSE_ID']] = $completion;
                }
            }


            //$all_user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
        }
        unset($completion);
        foreach($completions as &$completion){
            /*if(!in_array($completion['UF_USER_ID'], $users))
                continue;*/
            $completion['COMPLETION_ID'] = $completion['ID'];
            $completion['ID'] = $completion['UF_USER_ID'];
            $completion['NAME'] = $users[$completion['UF_USER_ID']]['NAME'];
            $completion['LAST_NAME'] = $users[$completion['UF_USER_ID']]['LAST_NAME'];
            $completion['EMAIL'] = $users[$completion['UF_USER_ID']]['EMAIL'];
            $completion['UF_WORK_START_DATE'] = $users[$completion['UF_USER_ID']]['UF_WORK_START_DATE'];

            $completion['UF_ROLE'] = $users[$completion['UF_USER_ID']]['UF_ROLE'];
            $value = false;
            if((int)$users[$completion['UF_USER_ID']]['UF_USER_RATING'] > 0){
                $array = CUserFieldEnum::GetList([], ["ID" => $users[$completion['UF_USER_ID']]['UF_USER_RATING']])->Fetch();
                $value = $array['VALUE'];
            }
            $completion['UF_USER_RATING'] = $value;
            $completion['PERSONAL_MOBILE'] = $users[$completion['UF_USER_ID']]['PERSONAL_MOBILE'];
            $completion['WORK_POSITION'] = $users[$completion['UF_USER_ID']]['WORK_POSITION'];
            $completion['DEALER'] = $dealers[$users[$completion['UF_USER_ID']]['UF_DEALER']];
            $completion['COURSE']['INFO'] = $courses[$completion['UF_COURSE_ID']];
            if(!check_full_array($user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']]))
                $user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
        }

        if(!$_REQUEST['status_complete']=='on'&&!$_REQUEST['status_not_complete']=='on') {
            foreach ($users as $user) {
                foreach ($courses as $course) {
                    if (!check_full_array($all_user_array[$user['ID'] . "_" . $course['ID']])) {
                        $completion['ID'] = $user['ID'];
                        $completion['NAME'] = $user['NAME'];
                        $completion['LAST_NAME'] = $user['LAST_NAME'];
                        $completion['EMAIL'] = $user['EMAIL'];
                        $completion['UF_WORK_START_DATE'] = $user['UF_WORK_START_DATE'];

                        $completion['UF_ROLE'] = $user['UF_ROLE'];
                        $completion['UF_USER_ID'] = $user['ID'];
                        $completion['UF_COURSE_ID'] = $course['ID'];
                        $completion['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
                        $value = false;
                        if((int)$user['UF_USER_RATING'] > 0){
                            $array = CUserFieldEnum::GetList([], ["ID" => $user['UF_USER_RATING']])->Fetch();
                            $value = $array['VALUE'];
                        }
                        $completion['UF_USER_RATING'] = $value;
                        $completion['WORK_POSITION'] = $user['WORK_POSITION'];
                        $completion['DEALER'] = $dealers[$user['UF_DEALER']];
                        $completion['COURSE']['INFO'] = $courses[$course['ID']];
                        if(check_full_array($user['MUST_COURSES'])&&in_array($course['ID'], $user['MUST_COURSES'])) {
                            $completion['NOT_ENROLLED'] = 1;
                        } else {
                            $completion['NOT_NEEDED'] = 1;
                        }
                        $completion['UF_IS_COMPLETE'] = false;
                        $user_array[$user['ID'] . "_" . $course['ID']] = $completion;
                        unset($completion);
                    }
                }
            }
        }
        //dump(array_keys($user_array));
        return $user_array;
    }

    public static function getRolesFilter(): array
    {
        $roles = [];
        $title_sop = '';
        if($_REQUEST['op_servis_op']=='on') {
            $op_roles = Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
            if(check_full_array($op_roles))
                $roles = array_values(array_unique(array_merge($roles, array_keys($op_roles))));
            if($_REQUEST['op_servis_servis']!='on'&&$_REQUEST['op_servis_marketing']!='on'){
                $title_sop = 'Отдел продаж';
            }
        }
        if($_REQUEST['op_servis_servis']=='on') {
            $ppo_roles = Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
            if(check_full_array($ppo_roles))
                $roles = array_values(array_unique(array_merge($roles, array_keys($ppo_roles))));
            if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_marketing']!='on'){
                $title_sop = 'Отдел послепродажного обслуживания';
            }
        }
        if($_REQUEST['op_servis_marketing']=='on') {
            $marketing_roles = Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
            if(check_full_array($marketing_roles))
                $roles = array_values(array_unique(array_merge($roles, array_keys($marketing_roles))));
            if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']!='on'){
                $title_sop = 'Отдел маркетинга';
            }
        }
        return ['roles' => $roles, 'title' => $title_sop];
    }

}
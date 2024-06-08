<?php

namespace Teaching;

use \Helpers\IBlockHelper;
use Helpers\UserHelper;

class SheduleCourses
{
    private static function get($filter, $select = [])
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $list = [];
        $arSelect = is_array($select)&&count($select)>0?$select:array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_BEGIN_REGISTRATION_DATE", "PROPERTY_END_REGISTRATION_DATE", "PROPERTY_COURSE", "PROPERTY_BEGIN_DATE", "PROPERTY_END_DATE", "PROPERTY_LIMIT");
        $arFilter = array("IBLOCK_ID" => IBlockHelper::getShedulesIBlock(), "ACTIVE" => "Y");
        if(check_full_array($filter))
            $arFilter = array_merge($arFilter, $filter);
        $res = \CIBlockElement::GetList(array('PROPERTY_BEGIN_DATE' => 'ASC'), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arFields = \Helpers\PropertyHelper::collectFields($arFields);
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getAllArray($filter = [], $select=[])
    {
        return self::getAll($filter, $select);
    }
    private static function getAll($filter, $select = [])
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $list = [];
        $arSelect = is_array($select)&&count($select)>0?$select:array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_BEGIN_REGISTRATION_DATE", "PROPERTY_END_REGISTRATION_DATE", "PROPERTY_COURSE", "PROPERTY_BEGIN_DATE", "PROPERTY_END_DATE", "PROPERTY_LIMIT");
        $arFilter = array("IBLOCK_ID" => IBlockHelper::getShedulesIBlock());
        if(check_full_array($filter))
            $arFilter = array_merge($arFilter, $filter);
        $res = \CIBlockElement::GetList(array('PROPERTY_BEGIN_DATE' => 'ASC'), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arFields = \Helpers\PropertyHelper::collectFields($arFields);
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getById($id)
    {
        return self::get(['ID' => $id]);
    }

    public static function getByCourse($course_id)
    {
        return self::getArray(['PROPERTY_COURSE' => $course_id]);
    }

    public static function getAvailableByCourse($course_id, $check_begin = false)
    {
        $filter = [
            'PROPERTY_COURSE' => $course_id,
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime(date('d.m.Y'), "YYYY-MM-DD")
        ];
        $list = self::getArray($filter);
        $schedules = [];

        foreach ($list as $item){
            if($check_begin){
                if (!empty($item['PROPERTIES']['BEGIN_REGISTRATION_DATE'])) {
                    if (time() < strtotime($item['PROPERTIES']['BEGIN_REGISTRATION_DATE'])) {
                        continue;
                    }
                }
            }
            if(!empty($item['PROPERTIES']['LIMIT'])&&$item['PROPERTIES']['LIMIT']>0) {
                if(self::getFreePlaces($item['ID'], $item['PROPERTIES']['LIMIT']) > 0)
                    $schedules[] = $item;
            }else{
                $schedules[] = $item;
            }
        }
        return $schedules;
    }

    public static function getAvailableByCourseByDate($course_id)
    {
        $filter = [
            'PROPERTY_COURSE' => $course_id,
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime(date('d.m.Y'), "YYYY-MM-DD")
        ];
        $list = self::getArray($filter);
        $schedules = [];
        foreach ($list as $item){
            $schedules[] = $item;
        }
        //dump($schedules);
        return $schedules;
    }
    public static function getAvailableByCourseByEndDate($course_id)
    {
        $filter = [
            'PROPERTY_COURSE' => $course_id,
            '<=PROPERTY_END_DATE' => ConvertDateTime(date('d.m.Y 23:59:59'), "YYYY-MM-DD")
        ];
        $list = self::getArray($filter);
        $schedules = [];
        foreach ($list as $item){
            $schedules[] = $item;
        }
        //dump($schedules);
        return $schedules;
    }
    public static function getAvailableOrProcessByCourse($course_id)
    {
        $filter = [
            'PROPERTY_COURSE' => $course_id,
            '>=PROPERTY_END_DATE' => ConvertDateTime(date('d.m.Y'), "YYYY-MM-DD")
        ];



        $list = self::getArray($filter);

        $schedules = [];
        foreach ($list as $item){

            if(!empty($item['PROPERTIES']['LIMIT'])&&$item['PROPERTIES']['LIMIT']>0) {
                if(self::getFreePlaces($item['ID'], $item['PROPERTIES']['LIMIT'])>0) {
                    $schedules[] = $item;
                }
            }else{
                $schedules[] = $item;
            }
        }
        return $schedules;
    }

    public static function getArray($filter = [], $select=[])
    {
        return self::get($filter, $select);
    }

    public static function getCoursesList($filter, $count = 0)
    {
        $list = self::get($filter, $count);
        foreach ($list as &$arFields) {
            $arFields['PROPERTY_BEGIN_DATE_VALUE'] = \Helpers\DateHelper::getHumanDate($arFields['PROPERTY_BEGIN_DATE_VALUE'], "d F");
            $arFields['PROPERTY_END_DATE_VALUE'] = \Helpers\DateHelper::getHumanDate($arFields['PROPERTY_END_DATE_VALUE']);
            $arFields['DETAIL_PAGE_LINK'] = '/courses/' . $arFields['CODE'] . '/';
            $arFields['IS_ONLINE'] = self::IsOnline($arFields['PROPERTY_BEGIN_DATE_VALUE'], $arFields['PROPERTY_END_DATE_VALUE']);
            $arFields['FOR_ROLES'] = self::getForRoles($arFields['PROPERTY_COURSE_VALUE']);
        }
        return $list;
    }

    public static function getFreePlaces($schedule_id, $all_places)
    {
        return max($all_places - self::getExistsPlaces($schedule_id),0);
    }

    public static function getFreePlacesBySchedule($schedule_id)
    {
        $schedule = current(self::getById($schedule_id));
        return (int)$schedule['PROPERTIES']['LIMIT'] - self::getExistsPlaces($schedule_id);
    }

    public static function getExistsPlaces($schedule_id)
    {
        $enrollments = new \Teaching\Enrollments();
        //dump($enrollments->getListByScheduleId($schedule_id));
        return count($enrollments->getListByScheduleId($schedule_id));
    }
    public static function getAllExistsPlaces($schedule_id)
    {
        $enrollments = new \Teaching\Enrollments();
        //dump($enrollments->getListByScheduleId($schedule_id));
        return count($enrollments->getAlllListByScheduleId($schedule_id));
    }
    public static function getAllApproveExistsPlaces($schedule_id)
    {
        $enrollments = new \Teaching\Enrollments();
        return count($enrollments->getAllApprovedListByScheduleId($schedule_id));
    }

    private static function getForRoles($course_id)
    {
        return Roles::getRolesForCourse($course_id);
    }

    private static function IsOnline($PROPERTY_BEGIN_DATE_VALUE, $PROPERTY_END_DATE_VALUE)
    {
        return false;
    }

    public static function createFreeSchedule($arFields)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        $el = new \CIBlockElement;
        $PROP = array();
        $PROP['COURSE'] = $arFields['ID'];
        $arLoadProductArray = array(
            "IBLOCK_ID" => \Helpers\IBlockHelper::getShedulesIBlock(),
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $arFields['NAME'],
            "ACTIVE" => "Y",            // активен
        );
        if (self::isNoExistFreeSchedule($arLoadProductArray))
            $el->Add($arLoadProductArray);
    }

    public static function findOrCreateExportSchedule($course_id, $date)
    {
        if(\Teaching\Courses::isFreeSheduleCourse($course_id)){
            return false;
        }
        //$schedule = current(self::get(['PROPERTY_COURSE' => $course_id, '>PROPERTY_BEGIN_DATE' => date("Y-m-d H:i:s", strtotime($date.' 00:00:01')), '<PROPERTY_BEGIN_DATE' => date("Y-m-d H:i:s", strtotime($date.' 23:59:59'))], ['ID', 'NAME']));

        $filter = [
            'PROPERTY_COURSE' => $course_id,
            [
                'LOGIC' => 'AND',
                ['<=PROPERTY_BEGIN_DATE' => date("Y-m-d H:i:s", strtotime($date.' 00:00:00'))],
                ['>=PROPERTY_END_DATE' => date("Y-m-d H:i:s", strtotime($date.' 23:59:59'))],
            ]
        ];
        $list = \Teaching\SheduleCourses::getArray($filter);
        if(!check_full_array($list)){
            $filter = [
                'PROPERTY_COURSE' => $course_id,
                '>=PROPERTY_BEGIN_DATE' => date("Y-m-d H:i:s", strtotime($date.' 00:00:00')),
                '<=PROPERTY_BEGIN_DATE' => date("Y-m-d H:i:s", strtotime($date.' 23:59:59')),
            ];
            $list = \Teaching\SheduleCourses::getArray($filter);
        }
        $schedule = current($list);
        if($schedule['ID']>0){
            return $schedule['ID'];
        }else {
            \Helpers\IBlockHelper::includeIBlockModule();
            $el = new \CIBlockElement;
            $PROP['COURSE'] = $course_id;
            $PROP['BEGIN_DATE'] = $date.' 12:00:00';
            $PROP['END_DATE'] = $date;
            $course = \Teaching\Courses::getById($course_id, true);
            $arLoadProductArray = array(
                "IBLOCK_ID" => \Helpers\IBlockHelper::getShedulesIBlock(),
                "PROPERTY_VALUES" => $PROP,
                "NAME" => $course['NAME'],
                "ACTIVE" => "Y",            // активен
            );
            return $el->Add($arLoadProductArray);
        }
    }

    private static function isNoExistFreeSchedule(array $arLoadProductArray)
    {
        $list = self::get(['PROPERTY_BEGIN_DATE' => false, 'PROPERTY_END_DATE' => false, 'PROPERTY_COURSE' => $arLoadProductArray['PROPERTY_VALUES']['COURSE']]);
        return count($list) == 0;
    }

    public static function getCourseIdBySchedule($schedule_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $db_props = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getShedulesIBlock(), $schedule_id, array("sort" => "asc"), array("CODE" => "COURSE"));
        if ($prop = $db_props->Fetch())
            return $prop['VALUE'];
    }

    public static function generateCode($fields)
    {
        return \CUtil::translit($fields['NAME'], 'ru', ["replace_space" => "_", "replace_other" => "_"]) . '_' . $fields['ID'];
    }

    public static function setCode($ID, $code)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        $el = new \CIBlockElement;
        $arLoadProductArray = array(
            "CODE" => $code
        );
        $res = $el->Update($ID, $arLoadProductArray);
    }

    public static function getByCourseAndDate($param, $date)
    {
        $list = self::get(['<=PROPERTY_BEGIN_DATE' => ConvertDateTime($date, "YYYY-MM-DD") . " 23:59:59", '>=PROPERTY_END_DATE' => ConvertDateTime($date, "YYYY-MM-DD") . " 23:59:59", 'PROPERTY_COURSE' => $param]);
        return array_shift($list);
    }

    public static function getByCoursesAndDate($param, $month, $year)
    {
        return self::get(['>=PROPERTY_BEGIN_DATE' => ConvertDateTime('01.'.$month.'.'.$year, "YYYY-MM-DD") . " 23:59:59", '<=PROPERTY_END_DATE' => ConvertDateTime(cal_days_in_month(CAL_GREGORIAN, $month, $year).'.'.$month.'.'.$year, "YYYY-MM-DD") . " 23:59:59", 'PROPERTY_COURSE' => $param]);
    }

    public static function getBeginDateBySchedule($id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $db_props = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getShedulesIBlock(), $id, array("sort" => "asc"), array("CODE" => "BEGIN_DATE"));
        if ($prop = $db_props->Fetch())
            return $prop['VALUE'];
    }

    public static function getScheduleIdsWithEnrolls()
    {
        $schedule_ids = [];
        $completions = new CourseCompletion();
        foreach ($completions->getAllStartedCompletions() as $enroll) {
            if ($enroll['UF_SHEDULE_ID'] > 0) {
                $schedule_ids[$enroll['UF_SHEDULE_ID']][] = $enroll;
            }
        }
        return $schedule_ids;
    }

    public static function getScheduleIdsByNewEnrolls()
    {
        $schedule_ids = [];
        $enrollmens = new Enrollments();
        foreach ($enrollmens->getAllNoneApprovedEnrolls() as $enroll) {
            if ($enroll['UF_SHEDULE_ID'] > 0)
                $schedule_ids[$enroll['UF_SHEDULE_ID']][] = $enroll;
        }
        return $schedule_ids;

    }

    public static function getNewExistsPlaces($schedule_id)
    {
        $enrollments = new \Teaching\Enrollments();
        return count($enrollments->getNewListByScheduleId($schedule_id));
    }

    public static function getSchedulesByMonth($month, $by_days = true)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);

        $year = $_GET['year']??date('Y');
        $first_day_of_month = '01.'.$month.'.'.$year;
        $last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year).'.'.$month.'.'.$year;

        $enrollments = new \Teaching\Enrollments();
        $courses_ids = \Teaching\Courses::getCoursesByUser($user_id);
        foreach ($courses_ids as &$courses_id) {
            $courses_id = (int)$courses_id;
        }
        $exists_ids = [];
        foreach($enrollments->getApprovedEnrolls() as $approvedEnroll){
            if($approvedEnroll['UF_SHEDULE_ID'])
                $exists_ids[] = $approvedEnroll['UF_SHEDULE_ID'];
        }
        $filter = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month, "YYYY-MM-DD"),
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month, "YYYY-MM-DD"),
        ];
        if(count($exists_ids)>0){
            $filter['!ID'] = $exists_ids;
        }
        return self::collectInfo($filter, $by_days);
    }

    public static function collectInfo($filter, $by_days)
    {
        $return_array = [];

        foreach(self::get($filter) as $schedule){
            $schedule['COURSE'] = \Teaching\Courses::getById($schedule['PROPERTIES']['COURSE']);
            if (!check_full_array($schedule['COURSE']))
                continue;
            if($by_days) {
                if (!empty($schedule['PROPERTIES']['BEGIN_DATE']) && !empty($schedule['PROPERTIES']['END_DATE'])) {
                    if ($schedule['PROPERTIES']['BEGIN_DATE'] != $schedule['PROPERTIES']['END_DATE']) {
                        foreach (\Helpers\DateHelper::getIntervalArray($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE']) as $day) {
                            $day = date('j.m.Y', strtotime($day));
                            $return_array[$day][] = $schedule;
                        }
                    } else {
                        $day = \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'j.m.Y');

                        $return_array[$day][] = $schedule;
                    }
                } else {
                    if (!empty($schedule['PROPERTIES']['BEGIN_DATE'])) {
                        $day = \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'j.m.Y');

                        $return_array[$day][] = $schedule;
                    }
                }
            }else{
                $return_array[] = $schedule;
            }
        }
        return $return_array;
    }

    public static function getSchedulesByMonthToTeachingAdmin($month, $by_days = true)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $year = $_GET['year']??date('Y');
        $first_day_of_month = '01.'.$month.'.'.$year;
        $last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year).'.'.$month.'.'.$year;
        $courses_ids = array_keys(\Models\Course::getByTeachingAdmin($user_id));
        $filter = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        $filter_end = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        $this_months_list = $temp_arr =  self::collectInfo($filter, $by_days);
        $this_months_end = self::collectInfo($filter_end, $by_days);
        if(check_full_array($this_months_end)){
            $this_months_end = array_reverse($this_months_end);
            foreach ($this_months_end as $day => $schedules) {

                if(!$this_months_list[$day]) {
                    $temp_arr = array_reverse($temp_arr);
                    $temp_arr[$day] = $schedules;
                    $temp_arr = array_reverse($temp_arr);
                }
            }
        }
        $this_months_list = $temp_arr;
        //dump($this_months_list);
        return $this_months_list;

    }

    public static function getSchedulesByMonthForList($month, $courses_for_role=[], $by_days = true)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $year = $_GET['year']??date('Y');
        if($month==date('m'))
            $first_day_of_month = date('d.m.Y');
        else
            $first_day_of_month = '01.'.$month.'.'.$year;
        $last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year).'.'.$month.'.'.$year;
        $enrollments = new \Teaching\Enrollments();
        if( !\Models\User::isTeachingAdmin() ) {
            $courses_ids = \Teaching\Courses::getCoursesByUser($user_id);
        } else {
            $courses_ids = $courses_for_role;
        }
        $exists_ids = [];
        foreach( $enrollments->getApprovedEnrolls() as $approvedEnroll ){
            $exists_ids[] = $approvedEnroll['UF_SHEDULE_ID'];
        }
        $ds = [];
        foreach ( $courses_ids as $id )
            if( is_array($courses_for_role)&&in_array($id, $courses_for_role) )
                $ds[] = $id;

        $courses_ids = $ds;
        $filter = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        $filter_end = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        if(!check_full_array($courses_for_role)){
            unset($filter['PROPERTY_COURSE']);
            unset($filter_end['PROPERTY_COURSE']);
        }
        if( count($exists_ids)>0 ){
            $filter['!ID'] = $exists_ids;
        }
        $this_months_list = $temp_arr = self::collectInfo($filter, $by_days);
        $this_months_end = self::collectInfo($filter_end, $by_days);
        if(check_full_array($this_months_end)){
            $this_months_end = array_reverse($this_months_end);
            foreach ($this_months_end as $day => $schedules) {
                if(!$this_months_list[$day]) {
                    $temp_arr = array_reverse($temp_arr);
                    $temp_arr[$day] = $schedules;
                    $temp_arr = array_reverse($temp_arr);
                }
            }
        }
        $this_months_list = $temp_arr;
        return $this_months_list;

    }

    public static function getNearest()
    {
        $date = ConvertDateTime(date('d.m.Y'), "YYYY-MM-DD");
        $filter = [
            '!PROPERTY_COURSE' => false,
            '>PROPERTY_BEGIN_DATE' => $date,
            '>PROPERTY_LIMIT' => 0,
        ];
        return self::get($filter);
    }

    public static function getNearestForUser(int $limit, $user_id=0)
    {
        $enrollments = new \Teaching\Enrollments();
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $courses_ids = \Teaching\Courses::getCoursesByUser($user_id);
        //dump($courses_ids);
        if(count($courses_ids)==0)
            return [];
        $exists_ids = [];
        foreach($enrollments->getApprovedEnrolls() as $approvedEnroll){
            $exists_ids[] = $approvedEnroll['UF_SHEDULE_ID'];
        }
        $date = date('Y-m-d');
        $filter = [
            'PROPERTY_COURSE'=>$courses_ids,
            '>PROPERTY_BEGIN_DATE' => $date,
        ];
        if(check_full_array($exists_ids)>0){
            $filter['!ID'] = $exists_ids;
        }
        return self::get($filter, $limit);
    }

    public static function getApprovedUsers($id)
    {
        $enrollments = new \Teaching\Enrollments();
        $approved_enrolls = $enrollments->getApprovedListByScheduleId($id);

        $user_ids = [];
        foreach ($approved_enrolls as $enroll)
            $user_ids[] = $enroll['UF_USER_ID'];
        $users_array = [];
        $dealers_array = [];
        if(check_full_array($user_ids)){
            $users_array = \Models\User::getArray(['filter' => ['ID' => $user_ids], 'select' => ['ID', 'UF_DEALER']]);
        }
        if(check_full_array($users_array)){
            foreach ($users_array as $user) {
                if((int)$user['UF_DEALER']<=0||$user['UF_DEALER']==292)
                    continue;
                $dealers_array[$user['UF_DEALER']][] = $user['ID'];
            }
        }
        return $dealers_array;
    }

    public static function setField($id, $code, $value)
    {
        if (!IBlockHelper::includeIBlockModule())
            return false;
        \CIBlockElement::SetPropertyValuesEx($id, self::getIblockId(), array($code => $value));
    }

    private static function getIblockId()
    {
        return IBlockHelper::getShedulesIBlock();
    }

    public static function getUsersForShedule($schedule)
    {
        $enrollments = new \Teaching\Enrollments();
        //уже записанные пользователи
        $exists_users = $enrollments->getApprovedUserListByScheduleId($schedule);
        //пользователи, которым указан курс в их ролях
        $roles_req  = \Teaching\Roles::getRolesForReqCourse(self::getCourseIdBySchedule($schedule));
        $get_by_roles_req = \Models\User::getEmployeesIdsByRoles(array_keys($roles_req));

        //список пользователей, которым курс указан в карточке пользователя
        $get_req_users = \Helpers\UserHelper::getIdsByCourse(self::getCourseIdBySchedule($schedule));
        //список по ролям, которые указаны в карточке курса как необязательные
        $roles_not_req = \Teaching\Roles::getRoleIdsForCourse(self::getCourseIdBySchedule($schedule));
        $users_not_req = \Models\User::getEmployeesIdsByRoles($roles_not_req);
        $all_need_users = array_merge($users_not_req, $get_req_users, $get_by_roles_req);
        return (array_diff($all_need_users, $exists_users));
    }

    public static function updatePictures($id, $pictures)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($id, self::getIblockId(), ['FILES' => $pictures]);
    }

    public static function updateComent($id, $text)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($id, self::getIblockId(), ['TRAINER_COMENT' => $text]);
    }

    public static function updateAllowMainTest($id, $value)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($id, self::getIblockId(), ['ALLOW_MAIN_TEST' => $value]);
    }

    public static function getNearestForCourse($course)
    {
        $date = date('Y-m-d', strtotime(date('d.m.Y')));
        $filter = [
            'PROPERTY_COURSE'=>$course,
            '>PROPERTY_BEGIN_DATE' => $date,
        ];
        return self::get($filter, 1);
    }

    public static function getIdsByTrainer($id)
    {
        $ids = [];
        foreach(self::get([], ['ID']) as $item) {
            //foreach(self::get(['PROPERTY_TRAINERS' => $id], ['ID']) as $item)
            $ids[] = $item['ID'];
        }
        return $ids;
    }

    private static function getMaxDCUsers($schedule_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $db_props = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getShedulesIBlock(), $schedule_id, array("sort" => "asc"), array("CODE" => "MAX_DC_EMPLS"));
        if ($prop = $db_props->Fetch())
            return (int)$prop['VALUE'];

    }

    public static function isAllowToEnrollUser($schedule_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $max_dc_users = \Teaching\SheduleCourses::getMaxDCUsers($schedule_id);
        if($max_dc_users==0)
            return true;
        $user = current(\Models\User::getArray(['filter' => ['ID' => $user_id], 'select' => ['ID', 'UF_DEALER']]));
        if(check_full_array($user)&&(int)$user['UF_DEALER']>0){
            $dealers = self::getApprovedUsers($schedule_id);
            if( !empty($dealers[$user['UF_DEALER']]) ) {
                return count($dealers[$user['UF_DEALER']])<$max_dc_users;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function isExistsCheckedByCourse($course_id)
    {
        $list = self::getNotCheckedByCourse($course_id);

        return check_full_array($list);
    }

    public static function getNotCheckedByCourse($course_id)
    {
        return self::getArray(['>PROPERTY_BEGIN_DATE' => date('Y-m-d H:i:s'), 'PROPERTY_COURSE' => $course_id, 'PROPERTY_DONT_CHECK_COMPLETIONS' => 'Y']);
    }

    public static function getActualOnWeek($num_weeks = 1)
    {
        $num_days = (7 * $num_weeks)-1;
        $first_day = date('Y-m-d', strtotime("tomorrow"));
        $last_day = date('Y-m-d', strtotime("+".$num_days." days", strtotime($first_day)));
        dump("Начальная дата выборки - ".$first_day);
        dump("Конечная дата выборки - ".$last_day."<br/><br/>");
        $filter = [
            '>=PROPERTY_BEGIN_DATE' => $first_day." 00:00:01",
            '<=PROPERTY_BEGIN_DATE' => $last_day." 23:59:59",
        ];
        return self::get($filter, 1);
    }

    public static function getTextToEmail($schedule_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $db_props = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getShedulesIBlock(), $schedule_id, array("sort" => "asc"), array("CODE" => "TEXT_TO_EMAIL"));
        if ($prop = $db_props->Fetch()) {
            return $prop['VALUE']['TEXT'];
        }
    }

    public static function isExistsCheckedByID($UF_SHEDULE)
    {
        $list = self::getArray(['ID' => $UF_SHEDULE, 'PROPERTY_DONT_CHECK_COMPLETIONS' => 'Y']);
        return check_full_array($list);
    }

    public static function isCompleted($id, $user_id = 0): bool
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $completion = current((new CourseCompletion())->get(['UF_SHEDULE_ID' => $id, 'UF_USER_ID' => $user_id, 'UF_IS_COMPLETE' => 1]));
        return $completion['ID']>0;
    }

    public static function wasStarted(mixed $ID): bool
    {
        $schedule = current(self::get(['ID' => $ID]));
        if($schedule['PROPERTIES']['BEGIN_DATE']){
            $begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
            return time()>$begin_tmstmp;
        }
        return false;
    }

    public static function wasEnded(mixed $ID): bool
    {
        $schedule = current(self::get(['ID' => $ID]));
        if($schedule['PROPERTIES']['END_DATE']){
            $begin_tmstmp = strtotime($schedule['PROPERTIES']['END_DATE']." 23:59:59");
            return time()>$begin_tmstmp;
        }
        return false;
    }

    public static function isAllowMainTest(mixed $schedule_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $prop = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getShedulesIBlock(), $schedule_id, array("sort" => "asc"), array("CODE" => "ALLOW_MAIN_TEST"))->Fetch();
        return (int)$prop['VALUE']==129;
    }

    public static function getDuration($schedule_id)
    {
        $schedule = current(self::get(['ID' => $schedule_id]));
        if($schedule_id > 0 && $schedule['ID'] > 0) {
            $start = new \DateTime($schedule['PROPERTY_BEGIN_DATE_VALUE']);
            $end = new \DateTime($schedule['PROPERTY_END_DATE_VALUE'] . ' 23:59:59');
            $interval = $start->diff($end);
            $days = $interval->days;
            return $days + 1;
        }
        return 1;
    }
}
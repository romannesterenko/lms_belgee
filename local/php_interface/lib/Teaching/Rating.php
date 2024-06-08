<?php

namespace Teaching;

use Models\User;

class Rating
{
    public array $arLevels;
    public array $arScoreLevels;
    public function __construct()
    {
        $this->arLevels = $this->getLevelEnum();
        $this->arScoreLevels = $this->getLevelsWithCouse();
    }

    public static function updateLevelsByRoles($role_id) {
        \CModule::includeModule('iblock');
        $property_ids = [
            1 => 44,
            2 => 45,
            3 => 46,
        ];
        $arSelect = Array("ID", "NAME", "PROPERTY_ROLE", "PROPERTY_COURSES_FOR_LEVEL_1", "PROPERTY_COURSES_FOR_LEVEL_2", "PROPERTY_COURSES_FOR_LEVEL_3", "PROPERTY_COURSES_FOR_LEVEL_4");
        $arFilter = Array("IBLOCK_ID" => 34, "PROPERTY_ROLE" => $role_id, "ACTIVE"=>"Y");
        $res = \CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        $need_level_courses = [];
        while ( $item = $res->fetch() ) {
            $need_level_courses[1] = $item['PROPERTY_COURSES_FOR_LEVEL_1_VALUE'];
            $need_level_courses[2] = array_merge($need_level_courses[1], $item['PROPERTY_COURSES_FOR_LEVEL_2_VALUE']);
            $need_level_courses[3] = array_merge($need_level_courses[2], $item['PROPERTY_COURSES_FOR_LEVEL_3_VALUE']);
            $need_level_courses[4] = check_full_array($item['PROPERTY_COURSES_FOR_LEVEL_4_VALUE'])?array_merge($need_level_courses[3], $item['PROPERTY_COURSES_FOR_LEVEL_4_VALUE']):[];
        }
        $course_ids = [];
        foreach ($need_level_courses as $level_courses){
            if(count($level_courses)>count($course_ids)){
                $course_ids = $level_courses;
            }
        }
        $reverse = array_reverse($need_level_courses, true);
        if(check_full_array($course_ids)){
            $cron_data = [];
            $users = \Models\User::getListByRole($role_id);
            foreach ($users as $user){
                $completions = (new \Teaching\CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_COURSE_ID' => $course_ids, 'UF_USER_ID' => $user["ID"]], ['ID', 'UF_USER_ID', 'UF_COURSE_ID']);
                $completion_course_ids = [];
                foreach ($completions as $completion)
                    $completion_course_ids[] = $completion['UF_COURSE_ID'];
                if(check_full_array($completion_course_ids)){
                    foreach ($reverse as $level => $need_course_ids){
                        if(count($need_course_ids) == 0)
                            continue;
                        $array_diff = array_diff($need_course_ids, $completion_course_ids);
                        if(count($array_diff)==0) {
                            $cron_data[] = ['user' => $user["ID"], 'level' => $level];
                            User::update($user['ID'], ['UF_USER_RATING' => $property_ids[$level]]);
                            break;
                        }
                    }
                }

            }
            \Helpers\Log::writeCommon($cron_data, 'cron/manager_level_update');
        }

    }

    //получить уровень отдаёт id элемента списка
    public function getUserLevelByUserId($iUserId){
        $userLevel = \Helpers\UserHelper::getUserValue($iUserId , 'UF_USER_RATING');
        if ($userLevel) {
            return $userLevel;
        } else {
            return false;
        }
    }
    //обновить уровень принимает id элемента списка
    public function updateLevelByUserId($iUserId , $iLevel){
        $userLevel = \Helpers\UserHelper::setUserValue( 'UF_USER_RATING' , $iLevel , $iUserId);
        if ($userLevel) {
            return  $userLevel;
        } else {
            return false;
        }
    }
    //получить возможные уровни
    protected function getLevelEnum() {
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => 218));
        $enum = array();
        while($arEnum = $rsEnum->Fetch()) {
            $enum[$arEnum["ID"]] = $arEnum["VALUE"];
        }
        return $enum;
    }
    public function getLevelsWithCouse() {
        return [
            0 => [],
            1 => \Bitrix\Main\Config\Option::get( "askaron.settings", "UF_FIRT_LEVEL"),
            2 => \Bitrix\Main\Config\Option::get( "askaron.settings", "UF_SECOND_LEVEL"),
            3 => \Bitrix\Main\Config\Option::get( "askaron.settings", "UF_THIRD_LEVEL")
        ];
    }
    //получить уровень пользователя с прогрессом по его роли
    public function setLevelEnumByUserRole($iUserId = false , $strUserRole = false) {
        $obComplitedCompletions = new \Teaching\CourseCompletion();
        $arComplitedCompletions = $obComplitedCompletions->get(['UF_IS_COMPLETE'=>1, 'UF_USER_ID' => $iUserId], ['*']);

        $arComplitedCourses = array_column($arComplitedCompletions , 'UF_COURSE_ID');

        $iLevel = 0;

        foreach ($this->arScoreLevels as $keyLevel => $arLevel) {
            if (array_diff($arLevel, $arComplitedCourses) == []) {
                $iLevel = $keyLevel;
            } else {
                break;
            }
        }

        return $this->updateLevelByUserId($iUserId , array_search($iLevel, $this->arLevels));
    }

    //аналогично getLevelEnumByUserRole только это getList аналог
    public function getUsersLevels($iUserId = false , $strUserRole = false) {

    }

}
<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$is_free = true;
$found = false;
$course_name = '';
$text = '';
$list = [];
if (\Teaching\Courses::isFreeSheduleCourse($request['course'])){

}else {
    $is_free = false;
    $filter = [
        'PROPERTY_COURSE' => $request['course'],
        [
            'LOGIC' => 'AND',
            ['<=PROPERTY_BEGIN_DATE' => $request['date'].' 00:00:00'],
            ['>=PROPERTY_END_DATE' => $request['date']],
        ]
    ];
    $list = \Teaching\SheduleCourses::getArray($filter);
    if(!check_full_array($list)){
        $filter = [
            'PROPERTY_COURSE' => $request['course'],
            '>=PROPERTY_BEGIN_DATE' => $request['date'].' 00:00:00',
            '<=PROPERTY_BEGIN_DATE' => $request['date'].' 23:59:59',
        ];
        $list = \Teaching\SheduleCourses::getArray($filter);
    }
    if(check_full_array($list)) {
        $item = current($list);
        $course_name = $item['NAME'];
        $text = 'С '.$item['PROPERTIES']['BEGIN_DATE'].' по '.$item['PROPERTIES']['END_DATE'].' по курсу "'.$item['NAME'].'" в системе найдено расписание, необходимое для добавления в запись прохождения.';
        $found = true;
    }else{
        $course = \Teaching\Courses::getById($request['course']);
        $found = false;
        $text = 'На '.date('d.m.Y', strtotime($request['date'])).' по курсу "'.$course['NAME'].'" в системе не найдено расписание, необходимое для добавления в запись прохождения. Система создаст расписание на '.date('d.m.Y', strtotime($request['date'])).' автоматически';

    }
}
echo json_encode(['is_free_course' => $is_free, 'list' => $list, 'found' => $found, 'course_name' => $course_name, 'text' => $text]);


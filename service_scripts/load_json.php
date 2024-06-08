<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

$last_names = [
    'Абдулин' => 'Абдуллин',
];
$names = [
    'Вдаислав' => 'Владислав',
];
$courses = [
    'Новая модель:  Geely Tugella (FY-11)' => 'Новая модель: Tugella (FY-11)',
    'Новая модель: Geely Coolray (SX-11)' => 'Новая модель:  Coolray (SX-11)',
    'Стандарты послепродажного обслуживания Geely' => 'Стандарты послепродажного обслуживания Geely  (8 шагов сервиса).',
    'Новая модель: Geely Atlas Pro (NL-3B)' => 'Новая модель: Atlas Pro (NL-3B)',
    'GMTC LEVEL-1 Экзамен' => 'GMTC LEVEL-1 (экзамен)',
    'GMTC LEVEL-1' => 'GMTC LEVEL-1 (курс 12 уроков)',
    'Ключевые процессы сервиса. Навыки продаж в сервисе' => 'Ключевые процессы сервиса. Навыки продаж в сервисе.',
    'Двигатели  3G15TD / JLH-4G20TD' => 'Двигатель 3G15TD / JLH-4G20TD',
    'Гарантийное сопровождение автомобилей GEELY' => 'Гарантийное сопровождение автомобилей GEELY (вебинар).',
    //'Базовый тренинг GEELY' => 'Базовый тренинг Geely для технических специалистов',
    'Работа с возражениями и конфликтами' => 'Работа с возражениями и конфликтами.',
    'Управление складскими запасами' => 'Управление складскими запасами.',
    'Экономика СТОА' => 'Экономика СТОА.',
    'Клиентоориентированный сервис' => 'Клиентоориентированный сервис.',
    'Вебинар: Правовые основы для сотрудников After Sales GEELY' => 'Вебинар: Правовые основы для сотрудников Sales GEELY',
];
$go = false;
if($USER->IsAdmin()){
    $json = file_get_contents("files/10761.json");
    $rows = json_decode($json, true);
    $completions = new \Teaching\CourseCompletion();
    foreach ($rows['RECORDS'] as $key => $row){

        if($row['course_status']=='Записан')
            continue;
        /*if($row['lastname']!='Касаткин')
            continue;*/


            $getListParams['select'] = ['ID', 'NAME', 'LAST_NAME'];
            $getListParams['filter'] = ['NAME' =>$row['firstname'], 'LAST_NAME' => $row['lastname']];
            $user = \Models\User::getArray($getListParams);

        if($last_names[$row['lastname']])
            $row['lastname'] = $last_names[$row['lastname']];
        if($names[$row['firstname']])
            $row['firstname'] = $names[$row['firstname']];
        $user = \Models\User::getByFullNameAndCode($row['firstname'], $row['lastname'], $row['dealer_name'], $row['dealer_code']);
        $user_id = $user['ID'];

        if(!check_full_array($user)){
            $user = \Models\User::getByFullName($row['firstname'], $row['lastname']);
            $user_id = $user['ID'];
        }

        $name = isset($courses[trim($row['cname'])])?$courses[trim($row['cname'])]:$row['cname'];

        $course = \Models\Course::getByName($name);
        if($course['ID']!=88&&$course['ID']!=96)
            continue;
        $item = $enroll = [];
        $find_array = [];
        $item['UF_POINTS'] = round($row['course_score']);
        $item['UF_USER_ID'] = $find_array['UF_USER_ID'] = $enroll['UF_USER_ID'] = $user_id;
        //$item['UF_DATE'] = $find_array['UF_DATE'] = $enroll['UF_DATE'] = date('d.m.Y', $row['startdate']);
        $item['UF_DATE'] = $find_array['UF_DATE'] = $enroll['UF_DATE'] = date('d.m.Y', $row['enddate']);
        //$item['UF_END_DATE'] = $enroll['UF_END_DATE'] = date('d.m.Y', $row['enddate']);
        if(!check_full_array($course)) {
            /*$el = new CIBlockElement;
            $fields = [
                'NAME' => $row['cname'],
                'ACTIVE' => 'N',
                'IBLOCK_ID' => COURSES_IBLOCK,
                'CODE' => CUtil::translit($row['cname'], 'ru'),
                'PROPERTY_VALUES' => [
                    'COURSE_TYPE' => 5
                ]
            ];
            $item['UF_COURSE_ID'] = $enroll['UF_COURSE_ID'] = $el->Add($fields);*/
        } else {
            $item['UF_COURSE_ID'] = $find_array['UF_COURSE_ID'] = $enroll['UF_COURSE_ID'] = $course['ID'];
        }

        //$item['UF_SHEDULE_ID'] = $enroll['UF_SHEDULE_ID'] = \Teaching\SheduleCourses::findOrCreateExportSchedule($item['UF_COURSE_ID'], $item['UF_DATE']);
        $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'].' 12:00:00';
        $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');
        $item['UF_TOTAL_ATTEMPTS'] = 1;
        $enroll['UF_IS_APPROVED'] = $find_array['UF_IS_COMPLETE'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
        if($row['cname']=='Базовый тренинг GEELY'){
            /*$completions->add($item);
            $completion = $completions->getByCourseAndUser($item['UF_COURSE_ID'], $item['UF_USER_ID']);
            if(check_full_array($completion))
                \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);*/
        }
        $find = $completions->get($find_array);
        if(check_full_array($find)){

            if(count($find)==1){
                /*$completions->update($find[0]['ID'], [
                    'UF_DATE' => date('d.m.Y', $row['enddate']),
                    'UF_COMPLETED_TIME' => date('d.m.Y', $row['enddate']).' 12:00:00',
                    'UF_DATE_UPDATE' => date('d.m.Y H:i:s')
                ]);
                \Helpers\Pdf::generateCertFromCompletionId($find[0]['ID']);*/
            }else{
                foreach ($find as $key_ => $item_){
                    if($key_==0){
                        /*$completions->update($item_['ID'], [
                            'UF_DATE' => date('d.m.Y', $row['enddate']),
                            'UF_COMPLETED_TIME' => date('d.m.Y', $row['enddate']).' 12:00:00',
                            'UF_DATE_UPDATE' => date('d.m.Y H:i:s')
                        ]);
                        \Helpers\Pdf::generateCertFromCompletionId($item_['ID']);*/
                    } else {
                        //$completions->delete($item_['ID']);
                    }
                }
            }
        } else {

            /*$item['UF_DATE'] = date('d.m.Y', $row['enddate']);
            $item['UF_COMPLETED_TIME'] = $item['UF_DATE'].' 12:00:00';
            $completions->add($item);
            $completion = current($completions->get(['UF_DATE' => $item['UF_DATE'], 'UF_COURSE_ID' => $item['UF_COURSE_ID'], 'UF_USER_ID' => $item['UF_USER_ID']]));
            if(check_full_array($completion))
                \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);*/
        }

    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
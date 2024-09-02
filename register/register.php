<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
if (!empty($_REQUEST['hash'])) {
    $rows = \Helpers\HLBlockHelper::get(
        \Helpers\HLBlockHelper::initialize('register_urls'),
        ['UF_HASH' => trim($_REQUEST['hash'])]
    );
    if (check_full_array($rows)) {
        $one_row = current($rows);
        if((int)$one_row['UF_SCHEDULE_ID'] > 0) {
            $schedules = \Teaching\SheduleCourses::getById($one_row['UF_SCHEDULE_ID']);
            if(check_full_array($schedules)) {
                if((int)$one_row['UF_USER_ID'] > 0) {
                    $schedule = current($schedules);
                    if(strtotime($schedule['PROPERTIES']['END_DATE']." 23:59:59") < time()) {
                        \Helpers\PageHelper::set404('Ссылка недействительна');
                    } else {
                        $completion = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_USER_ID' => $one_row['UF_USER_ID']]);
                        if(check_full_array($completion)){
                            if($USER->Authorize((int)$one_row['UF_USER_ID'])) {
                                LocalRedirect('/shedules/'.$schedule['ID'].'/');
                            }
                        } else {
                            \Helpers\PageHelper::set404('Вы не записаны на данный курс');
                        }
                    }
                }
            } else {
                \Helpers\PageHelper::set404('Информация о расписании не найдена');
            }
        } else {
            \Helpers\PageHelper::set404('Расписание не указано в базе');
        }
    } else {
        \Helpers\PageHelper::set404('Уникальный ключ не найден в базе');
    }
} else {
    \Helpers\PageHelper::set404('Уникальный ключ не указан');
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
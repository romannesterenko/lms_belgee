<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$courses = [
    'GMTC LEVEL-1 Экзамен' => 87,
    'Новая модель:  Geely Tugella (FY-11)' => 89,
    'Новая модель: Geely Atlas Pro (NL-3B)' => 90,
    'Новая модель: Geely Coolray (SX-11)' => 88,
    'Стандарты послепродажного обслуживания Geely' => 84,
    'Ключевые процессы сервиса. Навыки продаж в сервисе.' => 98,
    'Основы диагностики электрических систем GEELY' => 91,
    'Клиентоориентированный сервис.' => 102,
    'Клиентоориентированный сервис' => 102,
    'Экономика СТОА.' => 101,
    'Экономика СТОА' => 101,
    'Управление складскими запасами.' => 100,
    'Управление складскими запасами' => 100,
    'Работа с возражениями и конфликтами.' => 99,
    'Работа с возражениями и конфликтами' => 99,
    'Двигатели  3G15TD / JLH-4G20TD' => 93,
    'Трансмиссии 7DCT / AWF8F44' => 92,
    'Внутренние процессы сервиса.' => 97,
    'Основы работы с запчастями' => 103,
    'Работа с жалобами и конфликтами.' => 684,
    'Работа с жалобами и конфликтами' => 684,
    'Гарантийное сопровождение автомобилей GEELY' => 85,
    'Трансмиссии 7DCT / AWF8F50' => 9574,
];
if($USER->IsAdmin()){
    $enrolls = new \Teaching\Enrollments();
    $completions = new \Teaching\CourseCompletion();
    $oSpreadsheet = IOFactory::load("files/reestr.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('посетили с ID и без (2)')->getCellCollection();
    $max = $cells->getHighestRow();
    $array = [];
    for ($row = 2; $row <= $max; $row++){
        $item = $enroll = [];
        $array1 = explode('Прошел (оценка ', $cells->get('E' . $row)->getValue());
        if($array1[1]){
            $array2 = explode(')', $array1[1]);
            $item['UF_POINTS'] = $array2[0];
        }
        $item['UF_USER_ID'] = $enroll['UF_USER_ID'] = $cells->get('D' . $row)->getValue();
        $item['UF_DATE'] = $enroll['UF_DATE'] = Date::isDateTime($cells->get('F' . $row))?Date::excelToDateTimeObject($cells->get('F' . $row)->getValue())->format('d.m.Y'):$cells->get('F' . $row)->getValue();
        $item['UF_COURSE_ID'] = $enroll['UF_COURSE_ID'] = (int)$courses[trim($cells->get('B' . $row)->getValue())]>0?(int)$courses[trim($cells->get('B' . $row)->getValue())]:0;
        $item['UF_SHEDULE_ID'] = $enroll['UF_SHEDULE_ID'] = \Teaching\SheduleCourses::findOrCreateExportSchedule($item['UF_COURSE_ID'], $item['UF_DATE']);
        $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'].' 12:00:00';
        $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');

        $item['UF_TOTAL_ATTEMPTS'] = 1;
        $enroll['UF_IS_APPROVED'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
        $completions->add($item);
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
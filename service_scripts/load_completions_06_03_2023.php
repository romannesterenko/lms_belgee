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

$go = false;
if($USER->IsAdmin()){
    $oSpreadsheet = IOFactory::load("files/completions_06_03_2023.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('посетили с ID')->getCellCollection();
    $max = $cells->getHighestRow();
    $completions = new \Teaching\CourseCompletion();
    $array = [];
    for ($row = 2; $row <= $max; $row++){
        /*$a = [];
        foreach (range('A', 'H') as $letter){
            $code = $cells->get($letter . $row)->getValue();
            $a[$letter] = $cells->get($letter . $row)->getValue();
        }
        $array[] = $a;*/
        $item = $enroll = [];
        $array1 = explode('Прошел (', $cells->get('F' . $row)->getValue());
        if($array1[1]){
            $array2 = explode('%)', $array1[1]);
            $item['UF_POINTS'] = $array2[0];
        }
        $item['UF_USER_ID'] = $enroll['UF_USER_ID'] = $cells->get('D' . $row)->getValue();
        $item['UF_DATE'] = $enroll['UF_DATE'] = Date::isDateTime($cells->get('G' . $row))?Date::excelToDateTimeObject($cells->get('G' . $row)->getValue())->format('d.m.Y'):$cells->get('G' . $row)->getValue();
        $item['UF_COURSE_ID'] = $enroll['UF_COURSE_ID'] = (int)$cells->get('H' . $row)->getValue();
        $item['UF_SHEDULE_ID'] = $enroll['UF_SHEDULE_ID'] = \Teaching\SheduleCourses::findOrCreateExportSchedule($item['UF_COURSE_ID'], $item['UF_DATE']);
        $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'].' 12:00:00';
        $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');

        $item['UF_TOTAL_ATTEMPTS'] = 1;
        $enroll['UF_IS_APPROVED'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
        /*$completions->add($item);
        $completion = $completions->getByCourseAndUser($item['UF_COURSE_ID'], $item['UF_USER_ID']);
        if(check_full_array($completion))
            \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);*/
        dump($item);
    }

}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
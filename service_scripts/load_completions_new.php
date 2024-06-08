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

if($USER->IsAdmin()){
    $enrolls = new \Teaching\Enrollments();
    $completions = new \Teaching\CourseCompletion();
    foreach($completions->get(['UF_IS_COMPLETE'=>1, 'UF_COURSE_ID' => 82], ['ID', 'UF_USER_ID', 'UF_COURSE_ID']) as $completion){
        //\Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
    }
    /*$oSpreadsheet = IOFactory::load("files/dozapolnit.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('посетили с ID')->getCellCollection();
    $max = $cells->getHighestRow();
    $array = [];
    for ($row = 2; $row <= $max; $row++){
        $item = $enroll = [];
        $array1 = explode('Прошел (оценка ', $cells->get('D' . $row)->getValue());
        if($array1[1]){
            $array2 = explode(')', $array1[1]);
            $item['UF_POINTS'] = $array2[0];
        }
        $item['UF_USER_ID'] = $enroll['UF_USER_ID'] = $cells->get('B' . $row)->getValue();
        $item['UF_DATE'] = $enroll['UF_DATE'] = Date::isDateTime($cells->get('E' . $row))?Date::excelToDateTimeObject($cells->get('E' . $row)->getValue())->format('d.m.Y'):$cells->get('E' . $row)->getValue();
        $item['UF_COURSE_ID'] = $enroll['UF_COURSE_ID'] = 82;
        $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'].' 12:00:00';
        $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');

        $item['UF_TOTAL_ATTEMPTS'] = 1;
        $enroll['UF_IS_APPROVED'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
        $completions->add($item);
        $completion = $completions->getByCourseAndUser($item['UF_COURSE_ID'], $item['UF_USER_ID']);
        if(check_full_array($completion))
            \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
    }*/
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
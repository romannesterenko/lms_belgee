<?php

use Bitrix\Main\Application;
use Helpers\UserHelper;
use Models\Course;
use Models\User;
use Notifications\SiteNotifications;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Teaching\Courses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
$arIMAGE['MODULE_ID'] = 'main';
$fid = (int)$request['file_id'];
if((int)$_REQUEST['course_id'] > 0 ) {
    $tmpFilePath = $_FILES['file']['tmp_name'];
    if(!empty($tmpFilePath)) {
        $inputFileType = IOFactory::identify($tmpFilePath);
        $oSpreadsheet = IOFactory::createReader($inputFileType)->load($tmpFilePath);
        $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
        $cells = $oSpreadsheet->getSheet(0)->getCellCollection();
        $max = $cells->getHighestRow();
        $course = Course::find((int)$request['course_id'], ['ID', 'NAME']);
        for ($row = 2; $row <= $max; $row++) {
            $user = [];
            if ($cells->get("A" . $row) && (int)$cells->get("A" . $row)->getValue() > 0) {
                $user = User::find((int)$cells->get("A" . $row)->getValue(), ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_*']);
                if (!check_full_array($user) && $cells->get("B" . $row) && !empty($cells->get("B" . $row)->getValue())) {
                    $user = User::getByEmail($cells->get("B" . $row)->getValue());
                }
            }
            if (!check_full_array($user)) {
                echo "<div class='red'>Пользователь с ID " . (int)$cells->get("A" . $row)->getValue() . " и Еmail " . $cells->get("B" . $row)->getValue() . " не найден в системе</div><br/>";
            } else {
                $user_courses = $user['UF_REQUIRED_COURSES'];
                $user_courses[] = $_REQUEST['course_id'];
                $result = UserHelper::setUserValue('UF_REQUIRED_COURSES', $user_courses, $user['ID']);
                if ($result) {
                    $notifications = new SiteNotifications();
                    $course = Courses::getById($_REQUEST['course_id']);
                    $text = "Вам был назначен курс " . $course['NAME'] . '.';
                    $notifications->addNotification($user['ID'], $text, 'notify', '/courses/' . $course['CODE'] . '/');
                    echo "<div class='green'>Пользователю " . $user['NAME'] . " " . $user['LAST_NAME'] . " был назначен курс \"" . $course['NAME'] . "\"</div><br/>";
                }
            }

        }
    } else {
        echo "<div class='red'>Файл не выбран</div><br/>";
    }
} else {
    echo "<div class='red'>Курс не выбран</div><br/>";

}

?>



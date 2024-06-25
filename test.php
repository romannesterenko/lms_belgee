<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$completions = (new \Teaching\CourseCompletion())->get([
    'UF_SHEDULE_ID' => 124393
]);

foreach ($completions as $completion) {
    \Teaching\TestDrive\Group::setEmployeeToRandGroup($completion['UF_SHEDULE_ID'], $completion['UF_USER_ID']);
}


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
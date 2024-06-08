<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;



$completions = new \Teaching\CourseCompletion();

$compl = $completions->limit(['>ID' => 0, 'UF_IS_COMPLETE' => 1], ['ID'], 10000);
foreach ($compl as $one_completion) {
    //\Helpers\Pdf::generateCertFromCompletionIdNew($one_completion['ID']);
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
$completions = new \Teaching\CourseCompletion();

//\Helpers\Pdf::generateCertFromCompletionId(119);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
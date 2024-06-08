
<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER, $notifications_main_filter, $my_courses_filter, $needed_courses_filter, $courses_for_role_filter;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
//\Helpers\Pdf::generateCertFromCompletionId(42075);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
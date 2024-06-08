<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$USER->Authorize(4714);
header("Location: /bitrix/admin/");
exit;
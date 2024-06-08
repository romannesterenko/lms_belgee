<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$USER->Authorize(2);
header("Location: /bitrix/admin/");
exit;
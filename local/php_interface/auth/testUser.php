<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$USER->Authorize(8344);
header("Location: /");
exit;
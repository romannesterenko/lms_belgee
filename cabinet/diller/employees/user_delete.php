<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$id = \Helpers\UrlParamsHelper::getParam('id');
\Helpers\UserHelper::unsetUserDealer($id);
LocalRedirect('/cabinet/dealer/employees/');
?>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
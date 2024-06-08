<?php define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['dealer'] = $dealer = \Models\Dealer::find($request["id"]);
if($dealer['ID']>0) {
    \Models\Dealer::deactivate($dealer['ID']);
    \Models\Employee::unlinkFromDealer($dealer['ID']);
}

echo json_encode($response);


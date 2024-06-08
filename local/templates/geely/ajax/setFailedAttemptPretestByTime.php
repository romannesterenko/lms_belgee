<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if($request['user']>0&&$request['id']>0){
    $test_processed = new \Teaching\ProcessTest();
    $test = current($test_processed->getPreByTestAndUser($request['id'], $request['user']));
    if(check_full_array($test))
        $test_processed->setPreTestFinished($test['ID']);
}
echo json_encode(['success' => true]);
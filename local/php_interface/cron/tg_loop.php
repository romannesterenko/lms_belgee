<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline/session.madeline_production';
$appInfo = new danog\MadelineProto\Settings\AppInfo();
$appInfo->setApiId(26313809);
$appInfo->setApiHash("7dc6e748d8b387b27befa3f25f2eecc3");
$settings = new danog\MadelineProto\Settings;
$settings->getPeer()->setCacheAllPeersOnStartup(true);
$settings->getPeer()->setFullFetch(true);
$settings->getPeer()->setFullInfoCacheTime(3600);
$settings->getLogger()->setLevel(danog\MadelineProto\Logger::LEVEL_ULTRA_VERBOSE);
$settings->getLogger()->setExtra($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/madeline/MadelineProto.log");
$settings->setAppInfo($appInfo);
class MyEventHandler extends danog\MadelineProto\EventHandler {

}
MyEventHandler::startAndLoop($session_file, $settings);
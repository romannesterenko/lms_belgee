<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new_session/session.madeline_production';
$appInfo = new danog\MadelineProto\Settings\AppInfo();
$appInfo->setApiId(22295348);
$appInfo->setApiHash("e17f56977ba619fe9ad04b7a768c12bf");
$settings = new danog\MadelineProto\Settings();
/*$settings->getPeer()->setCacheAllPeersOnStartup(true);
$settings->getPeer()->setFullFetch(true);
$settings->getPeer()->setFullInfoCacheTime(3600);
$settings->getLogger()->setLevel(danog\MadelineProto\Logger::LEVEL_ULTRA_VERBOSE);
$settings->getLogger()->setExtra($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/madeline_new_session/file.txt");*/
$settings->setAppInfo($appInfo);

class MyEventHandler extends danog\MadelineProto\EventHandler {

}
MyEventHandler::startAndLoop($session_file, $settings);
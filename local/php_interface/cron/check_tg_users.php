<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline/session.madeline_production';
$_SERVER["HTTP_HOST"] = $_SERVER["DOCUMENT_ROOT"];
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
?>
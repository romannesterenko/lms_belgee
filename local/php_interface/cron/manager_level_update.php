<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Teaching\Rating::updateLevelsByRoles(1);



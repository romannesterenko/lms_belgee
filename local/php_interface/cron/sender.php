<?php
$_SERVER["DOCUMENT_ROOT"] = '/home/u520251/lms.geely-motors.com/www';
$_SERVER["REMOTE_ADDR"] = '/home/u520251/lms.geely-motors.com/www';
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Cron\Tasks::checkNeedSender();
//\Cron\Tasks::startSenderNHoursScript();
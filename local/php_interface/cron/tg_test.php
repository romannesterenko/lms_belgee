<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/u520251/lms.geely-motors.com/www";
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/tg_my_test_session/session.madeline_production';
$MadelineProto = new \danog\MadelineProto\API($session_file);
try {
    print_r($MadelineProto->getFullInfo("https://t.me/+CahKcN62k0Q2NDYy"));
} catch (Exception $e) {
    print_r($e->getMessage());
}
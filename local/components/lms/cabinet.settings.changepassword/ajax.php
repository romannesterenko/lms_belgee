<?php

use Settings\UserPassword;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response['values'] = $_REQUEST;
$response['success'] = false;
$response['div_class'] = 'error';
$user_psswd = new UserPassword();
foreach ($_REQUEST as $field){
    if(empty($field))
        $empty_field = true;
}
if (!$user_psswd->isUserPassword($_REQUEST['old'])) {
    $response['message'] = GetMessage('OLD_PASS_IS_INCORRECT');
} else {
    if (strcmp($_REQUEST['new_pass'], $_REQUEST['renew_pass']) != 0) {
        $response['message'] = GetMessage('PASSWORDS_NOT_EQUALS');
    }else{
        $answer = $user_psswd->updateUserPassword($_REQUEST);
        $response['message'] = strip_tags($answer)==1?GetMessage('PASSWORD_UPDATED_SUCCESS'):strip_tags($answer);
        $response['div_class'] = strip_tags($answer)==1?'success':'error';
    }
}

echo json_encode($response);

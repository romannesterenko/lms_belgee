<?php

use Integrations\Scorm;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if((int)$_REQUEST['user_id']>0&&(int)$_REQUEST['course_id']>0){
    $sc = new Scorm();
    $data = $sc->getData((int)$_REQUEST['user_id'], (int)$_REQUEST['course_id'], (int)$_REQUEST['part']);
    /*if((int)$_REQUEST['user_id']==2) {
        $data['score_raw'] = 0;
        $data['suspend_data'] = '';
    }*/
    echo json_encode($data);
}

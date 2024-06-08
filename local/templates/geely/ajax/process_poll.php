<?php

use Bitrix\Main\Application;
use Polls\ProcessPoll;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$poll_process = new ProcessPoll();
$response['success'] = $poll_process->process($request);
$response['finished'] = false;
if($request['cur_question']==--$request['all_questions'])
    $response['finished'] = true;
echo json_encode($response);



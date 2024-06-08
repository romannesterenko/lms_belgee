<?php

use Integrations\Scorm;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$sc = new Scorm();
$data['created_at'] = date('d.m.Y H:i:s');
$sc->addData($data);



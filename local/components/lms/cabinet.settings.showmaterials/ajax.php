<?php

use Settings\ShowMaterials;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response['values'] = $_REQUEST['value'];
$response['user'] = $user = (int)$USER->GetID();
$show_materials = new ShowMaterials();
$response['success'] = $show_materials->updateShowMaterialsSettingsByUser($response);
echo json_encode($response);

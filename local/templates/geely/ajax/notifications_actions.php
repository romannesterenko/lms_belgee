<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Notifications\SiteNotifications;
$notifications = new SiteNotifications();
$response['request'] = $_REQUEST;
if($_REQUEST['action']='make_read') {
    $response['success'] = false;
    if ($_REQUEST['id'] > 0)
        $response['success'] = $notifications->makeAsRead($_REQUEST['id']);
}
echo json_encode($response);


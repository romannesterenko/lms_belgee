<?php
$_SERVER["DOCUMENT_ROOT"] = '/home/u520251/lms.geely-motors.com/www';
$_SERVER["REMOTE_ADDR"] = '/home/u520251/lms.geely-motors.com/www';
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$meeting_id = \Integrations\Zoom::getDealerClubMeeting();
$account_id = \Integrations\Zoom::getDealerClubAccountId();
if(\Integrations\Zoom::getAllowCronApprovement()=='Y'&&!empty($meeting_id)&&(int)$account_id>0) {
    if(\Integrations\Zoom::getEventType()=='meeting') {
        $array = \Integrations\Zoom::getMeetingRegistrants($meeting_id, (int)$account_id);
        $result = json_decode(json_encode($array), true);
        if (check_full_array($result['registrants'])) {
            $users_to_approve = [];
            $users_to_deny = [];
            foreach ($result['registrants'] as $registrant) {
                if (\Models\Employee::isExistsForZoom($registrant['email']))
                    $users_to_approve[] = ['id' => $registrant['id'], 'email' => $registrant['email']];
                /*else
                    $users_to_deny[] = ['id' => $registrant['id'], 'email' => $registrant['email']];*/
            }
            if (check_full_array($users_to_approve)) {
                \Integrations\Zoom::approveRegistrantBatch($meeting_id, $users_to_approve);
            }
            if (check_full_array($users_to_deny)) {
                sleep(10);
                \Integrations\Zoom::denyRegistrantBatch($meeting_id, $users_to_deny);
            }
        }
    }
    if(\Integrations\Zoom::getEventType()=='webinar'){
        $array = \Integrations\Zoom::getWebinarRegistrants($meeting_id, (int)$account_id);
        $result = json_decode(json_encode($array), true);
        if (check_full_array($result['registrants'])) {
            $users_to_approve = [];
            $users_to_deny = [];
            foreach ($result['registrants'] as $registrant) {
                if (\Models\Employee::isExistsForZoom($registrant['email']))
                    $users_to_approve[] = ['id' => $registrant['id'], 'email' => $registrant['email']];
                /*else
                    $users_to_deny[] = ['id' => $registrant['id'], 'email' => $registrant['email']];*/
            }
            if (check_full_array($users_to_approve)) {
                \Integrations\Zoom::approveWebinarRegistrantBatch($meeting_id, $users_to_approve, (int)$account_id);
            }
            if (check_full_array($users_to_deny)) {
                sleep(10);
                \Integrations\Zoom::denyWebinarRegistrantBatch($meeting_id, $users_to_deny, (int)$account_id);
            }
        }
    }
}

<?php

use Integrations\Zoom;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
$meeting_id = Zoom::getDealerClubMeeting();
$account_id = Zoom::getDealerClubAccountId();

if(Zoom::getEventType()=='webinar') {
    $array = Zoom::getWebinarDenyRegistrants($meeting_id, $account_id);
}else{
    $array = Zoom::getMeetingDenyRegistrants($meeting_id);
}
$result = json_decode(json_encode($array), true);
$users_to_approve = [];
if(check_full_array($result['registrants'])) {
    foreach ($result['registrants'] as $registrant) {
        if (\Models\Employee::isExistsForZoom($registrant['email'])) {
            $users_to_approve[] = ['id' => $registrant['id'], 'email' => $registrant['email']];
        }
    }
}
$cnt = 0;
if(check_full_array($users_to_approve)){
    if(Zoom::getEventType()=='webinar') {
        $array = Zoom::approveWebinarRegistrantBatch($meeting_id, $users_to_approve, $account_id);
    } else {
        Zoom::approveRegistrantBatch($meeting_id, $users_to_approve);
    }

    $cnt++;
}
echo 'Подверждено '.$cnt.' отклоненных пользователей';

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
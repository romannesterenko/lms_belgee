<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Helpers\HLBlockHelper as HLBlock;
use Models\Course;
use Models\User;
use Notifications\EmailNotifications;
use Settings\Common;

if(Common::get('enable_subscription_mode') == 'Y'){
    $days = ((int)Common::get('how_long_to_remind_subscription'))+1;
    $string = $days==31?'+ 1 month':'+'.$days.' days';
    $need_date = date('d.m.Y', strtotime($string))." 00:00:00";
    $rows = HLBlock::get(HLBlock::initialize('course_subscription'), [
        'UF_DATE' => $need_date,
        'UF_COURSE_COMPLETED' => 0,
        'UF_COMPLETE_REMIND_SENT' => 0,
    ]);
    if (check_full_array($rows)){
        foreach ($rows as $row) {
            $user = User::find($row['UF_USER_ID']);
            $course = Course::find($row['UF_COURSE_ID'], ['ID', 'NAME', 'CODE']);
            $fields = [
                'USER_NAME' => $user['NAME']." ".$user['LAST_NAME'],
                'COURSE_CODE' => $course['CODE'],
                'COURSE_NAME' => $course['NAME'],
                'DATE' => date('d.m.Y', strtotime((string)$row['UF_DATE'])),
            ];
            EmailNotifications::send("REMIND_SUBSCRIBTION_COURSE_TO_USER", $user['EMAIL'], $fields);
            HLBlock::update(
                $row['ID'],
                [
                    'UF_COMPLETE_REMIND_SENT' => 1,
                    'UF_COMPLETE_REMIND_SENT_AT' => date('d.m.Y H:i:s'),
                ],
                HLBlock::initialize('course_subscription')
            );
        }
    }
}

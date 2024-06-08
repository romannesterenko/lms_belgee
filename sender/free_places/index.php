<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Models\User;
use Notifications\EmailNotifications;
use Teaching\Courses;
use Teaching\SheduleCourses;

$need_to_send = false;
if($_REQUEST['sended'] == 'Y') {
    $text_message = '<br/>';
    $shedule_ids = [];
    foreach ($_REQUEST as $name => $value) {
        $arr = explode('schedule_', $name);
        if($arr[1]>0)
            $shedule_ids[] = $arr[1];
    }
    if(check_full_array($shedule_ids)){
        $schedules = SheduleCourses::getArray(['ID' => $shedule_ids]);
        if (check_full_array($schedules)){
            foreach ($schedules as $schedule){
                if(\Models\Course::isOP($schedule['PROPERTY_COURSE_VALUE']))
                    $text_message.="<a href='https://lms.geely-motors.com/shedules/".$schedule['ID']."/' target='_blank'>".$schedule['NAME']." ".$schedule['PROPERTY_BEGIN_DATE_VALUE']."</a><br/>";
            }
        }
    }
    echo "Текст письма";
    echo "<br />";
    echo "------------------";
    echo "<br />";
    echo "Здравствуйте, #NAME# #LAST_NAME#.";
    echo "<br />";
    echo "<br />";

    echo "В курсах из приведенного ниже списка есть свободные места";
    echo "<br />";
    echo $text_message;
    echo "<br />";
    echo "Статус рассылки";
    echo "<br />";
    echo "------------------";
    echo "<br />";
    foreach (User::getOPAdmins() as $user){
        EmailNotifications::sendInfoToTeachingAdminAboutFreePlaces($user['ID'], $text_message);
        echo GetMessage('SENDED_MAIL_TO_USER').' '.$user['LAST_NAME'].' '.$user['NAME'].'<br/>';
    }
}
$select = [];
$new_select = [];
$list = SheduleCourses::getNearest();
foreach ($list as $schedule){
    if (SheduleCourses::getFreePlacesBySchedule($schedule['ID'])==0)
        continue;
    $schedule['COURSE'] = current(Courses::getList(['ID' => $schedule['PROPERTIES']['COURSE']], ['ID', 'NAME']));
    $new_select[$schedule['COURSE']['NAME']][] = $schedule;
    //$select[] = $schedule;
}
ksort($new_select);
foreach ($new_select as $course_name=>$items){
    foreach ($items as $item)
        $select[] = $item;
}
    if(count($select)>0) {?>
        <h2 class="h2"><?=GetMessage('TITLE_EXIST_PLACES')?></h2>
        <form method="post" class="form-group">
            <input type="hidden" name="sended" value="Y">
            <?php foreach ($select as $item){?>
                <div class="form-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="poll-question-<?=$item['ID']?>" name="schedule_<?=$item['ID']?>">
                        <label for="poll-question-<?=$item['ID']?>"><?=GetMessage('COURSE')?> "<?=$item['COURSE']['NAME']?>", <?=GetMessage('BEGIN')?> <?=$item['PROPERTIES']['BEGIN_DATE']?></label>
                    </div>
                </div>
            <?php }?>
            <button type="submit" class="btn btn--md"><?=GetMessage('SEND_BUTTON_TITLE')?></button>
        </form>
    <?php }else{?>
        <h2 class="h2"><?=GetMessage('TITLE_NOT_EXIST_PLACES')?></h2>
    <?php }?>

<a href="/sender/" class="btn btn--md btn--fixed"><?=GetMessage('BACK_BUTTON_TITLE')?></a><br/><br/>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;
use Helpers\DealerHelper;
use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

global $USER;

//if($USER->GetID()!=1 and $USER->GetID()!=4) LocalRedirect("/");

?>

<?php
    $rows = [];
    $user_ids = [];
    $course_ids = [];
    $dealer_ids = [];
    $schedule_ids = [];
    $users = [];
    $courses = [];
    $dealers = [];
    $schedules = [];
    $completions = new CourseCompletion();
    $enrollments = new Enrollments();

    /*foreach($completions->getAllCompletions() as $completion){
        $user_ids[] = $completion['UF_USER_ID'];
        $course_ids[] = $completion['UF_COURSE_ID'];
        $completion['TYPE'] = 'completion';
        if($completion['UF_SHEDULE_ID']>0)
            $schedule_ids[] = $completion['UF_SHEDULE_ID'];
        $rows[] = $completion;
    }*/
    foreach($enrollments->get(['>ID'=>0]) as $enroll){
        $user_ids[] = $enroll['UF_USER_ID'];
        $course_ids[] = $enroll['UF_COURSE_ID'];
        if($enroll['UF_SHEDULE_ID']>0)
            $schedule_ids[] = $enroll['UF_SHEDULE_ID'];
        $rows[] = $enroll;
    }
    if(count($user_ids)>0){
        foreach (User::getByIds(array_unique($user_ids), ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER']) as $user){
            if(!$users[$user['ID']]['ID']>0) {
                $dealer_ids[] = $user['UF_DEALER'];
                $users[$user['ID']] = $user;
            }
        }
    }
    if(count($dealer_ids)>0){
        foreach (DealerHelper::getList(['ID'=>array_unique($dealer_ids)], ['ID', 'NAME','PROPERTY_DISTRICT']) as $dealer){
            if(!$dealers[$dealer['ID']]['ID']>0) {
                $dealers[$dealer['ID']] = $dealer;
            }
        }
    }
    if(count($course_ids)>0){
        foreach (Courses::getList(['ID' => array_unique($course_ids)], ['ID', 'NAME', 'CODE']) as $course){
            if(!$courses[$course['ID']]['ID']>0) {
                $courses[$course['ID']] = $course;
            }
        }
    }
    if(count($schedule_ids)>0){
        foreach (SheduleCourses::getArray(['ID' => array_unique($schedule_ids)]) as $schedule){
            if(!$schedules[$schedule['ID']]['ID']>0) {
                $schedules[$schedule['ID']] = $schedule;
            }
        }
    }
    foreach ($rows as $key => &$row1) {
        $users[$row1['UF_USER_ID']]['DEALER'] = $dealers[$users[$row1['UF_USER_ID']]['UF_DEALER']];
        $row1['USER'] = $users[$row1['UF_USER_ID']];

        if($schedules[$row1['UF_SHEDULE_ID']]['ID']>0)
            $row1['SCHEDULE'] = $schedules[$row1['UF_SHEDULE_ID']];
        if($courses[$row1['UF_COURSE_ID']]['ID']>0)
            $row1['COURSE'] = $courses[$row1['UF_COURSE_ID']];
        else
            unset($rows[$key]);
    }
    $rows = array_values($rows);
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase"><?= Loc::getMessage('H2_TITLE') ?></h2>
            <div class="table-block">
                <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                    <thead class="thead-dark">
                    <tr>
<!--                        <th>#</th>-->
                        <th><?= Loc::getMessage('DEALER') ?></th>
                        <th><?= Loc::getMessage('REGION') ?></th>
                        <th><?= Loc::getMessage('EMPLOYEE') ?></th>
                        <th><?= Loc::getMessage('COURSE') ?></th>
                        <th><?= Loc::getMessage('BEGIN_DATE') ?></th>
                        <th><?= Loc::getMessage('APPROVED') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $key => $row){
                            if($row['SCHEDULE']['PROPERTIES']['BEGIN_DATE']):?>
                            <tr style="height: 50px">
<!--                                <td class="text-center">--><?php //=($key+1)?><!--</td>-->
                                <td class="text-left"><?=$row['USER']['DEALER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['DEALER']['PROPERTY_DISTRICT_VALUE']?></td>
                                <td class="text-left" data-id="<?=$row['USER']["ID"]?>"><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></td>
                                <td class="text-left"><a href="/courses/<?=$row['COURSE']['CODE']?>/" target="_blank"><?=$row['COURSE']['NAME']?></a></td>
                                <td><?php if($row['SCHEDULE']['ID']>0){echo DateHelper::getHumanDate($row['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd.m.Y');}?></td>
                                <td>
                                    <?php if($row['UF_IS_APPROVED']==1){?>
                                        <span class="status status--passed"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt=""></span><?= Loc::getMessage('APPR') ?></span>
                                    <?php }?>
                                </td>
                            </tr>
                            <?php endif;?>
                        <?php }?>
                    </tbody>
                </table>

            </div>


        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
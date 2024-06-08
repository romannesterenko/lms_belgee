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
    $filter = ['>ID'=>0];
    if($_REQUEST['month']&&$_REQUEST['year']){
        $filter['>=UF_DATE'] = '01'.$_REQUEST['month'].'.'.$_REQUEST['year'];
        $filter['<=UF_DATE'] = '31'.$_REQUEST['month'].'.'.$_REQUEST['year'];
    }
    if($_REQUEST['status']&&$_REQUEST['status']!='all')
        $filter['UF_IS_APPROVED'] = $_REQUEST['status']=='yes';
    foreach($enrollments->get($filter) as $enroll){
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
        foreach (DealerHelper::getList(['ID'=>array_unique($dealer_ids)], ['ID', 'NAME']) as $dealer){
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
    $months = [
        "01" => "Январь",
        "02" => "Февраль",
        "03" => "Март",
        "04" => "Апрель",
        "05" => "Май",
        "06" => "Июнь",
        "07" => "Июль",
        "08" => "Август",
        "09" => "Сентябрь",
        "10" => "Октябрь",
        "11" => "Ноябрь",
        "12" => "Декабрь",
    ];
    $years = range(2019, (int)date('Y')+1);
    $selected_month = date('m');
    if($_REQUEST['month'])
        $selected_month = $_REQUEST['month'];
    $selected_year = date('Y');
    if($_REQUEST['year'])
        $selected_year = $_REQUEST['year'];
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase"><?= Loc::getMessage('H2_TITLE') ?></h2>
            <div class="table-block">
                    <form class="report_generator" action="" method="get" style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Месяц</label>
                            <div class="select">
                                <select class="select2" name="month">
                                    <?php foreach ($months as $id => $month){?>
                                        <option value="<?=$id?>"<?=$selected_month==$id?' selected':''?>><?=$month?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Год</label>
                            <div class="select">
                                <select class="select2" name="year">
                                <?php foreach ($years as $year){?>
                                    <option value="<?=$year?>"<?=$year==$selected_year?' selected':''?>><?=$year?></option>
                                <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Статус заявки</label>
                            <div class="select">
                                <select class="select2" name="status">
                                    <option value="all">Все заявки</option>
                                    <option value="yes"<?=$_REQUEST['status']=='yes'?' selected':''?>>Подтверждена</option>
                                    <option value="no"<?=$_REQUEST['status']=='no'?' selected':''?>>Не подтверждена</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%;">
                            <label for="">&nbsp;</label>
                                <button class="btn" style="height: 36px">Генерировать</button>
                        </div>
                    </form>
                <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                    <thead class="thead-dark">
                    <tr>
                        <th><?= Loc::getMessage('DEALER') ?></th>
                        <th><?= Loc::getMessage('EMPLOYEE') ?></th>
                        <th><?= Loc::getMessage('COURSE') ?></th>
                        <th><?= Loc::getMessage('DATE_BEGIN') ?></th>
                        <th><?= Loc::getMessage('APPROVED') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $key => $row){
                            if($row['SCHEDULE']['PROPERTIES']['BEGIN_DATE']):?>
                            <tr style="height: 50px">
<!--                                <td class="text-center">--><?php //=($key+1)?><!--</td>-->
                                <td class="text-left"><?=$row['USER']['DEALER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></td>
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
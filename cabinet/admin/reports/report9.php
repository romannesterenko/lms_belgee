<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;
use Helpers\DealerHelper;
use Models\Dealer;
use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

global $USER;
$_REQUEST['report_id'] = 9999;
//if($USER->GetID()!=1 and $USER->GetID()!=4) LocalRedirect("/");

?>

<?php
$rows = [];
$user_ids = [];
$course_ids = [];
$dealer_ids = [];
$role_ids = [];
$schedule_ids = [];
$users = [];
$courses = [];
$dealers = [];
$roles = [];
$schedules = [];
$completions = new CourseCompletion();
$enrollments = new Enrollments();
$filter = ['>ID'=>0];
if($_REQUEST['month']&&$_REQUEST['year']){
    $start = '01.'.$_REQUEST['month'].'.'.$_REQUEST['year'];
    $end = '31.'.$_REQUEST['month'].'.'.$_REQUEST['year'];
    $filter_qwe = [
        '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($start, "YYYY-MM-DD"),
        '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($end, "YYYY-MM-DD"),
    ];
    $list = SheduleCourses::getArray($filter_qwe);
    if(check_full_array($list)){
        foreach ($list as $schedule){
            $filter['UF_SHEDULE_ID'][] = $schedule['ID'];
        }
    }
    /*dump($filter_qwe);
    dump($list);*/
    //$filter['>=UF_DATE'] = '01.'.$_REQUEST['month'].'.'.$_REQUEST['year'];
    //$filter['<=UF_DATE'] = '31.'.$_REQUEST['month'].'.'.$_REQUEST['year'];
}
if($_REQUEST['date']){
    $filter['UF_DATE'] = str_pad($_REQUEST['date'], 2, '0', STR_PAD_LEFT).'.'.$_REQUEST['month'].'.'.$_REQUEST['year'];

    unset($filter['>=UF_DATE']);
    unset($filter['<=UF_DATE']);
}
if($_REQUEST['status']&&$_REQUEST['status']!='all') {
    if($_REQUEST['status']=='approved')
        $filter['UF_IS_APPROVED'] = 1;
    elseif($_REQUEST['status']=='not_approved')
        $filter['UF_IS_APPROVED'] = false;
}
if($_REQUEST['course_id']&&(int)$_REQUEST['course_id']>0) {
    $filter['UF_COURSE_ID'] = (int)$_REQUEST['course_id'];
}
$only_compls = false;
if($filter['UF_COURSE_ID']>0&& \Teaching\Courses::isFreeSheduleCourse($filter['UF_COURSE_ID'])){
    $items = $completions->get($filter);
    $only_compls = true;
} else {
    $items = $enrollments->get($filter);
}
foreach($items as $enroll){
    $user_ids[] = $enroll['UF_USER_ID'];
    $course_ids[] = $enroll['UF_COURSE_ID'];
    if($enroll['UF_SHEDULE_ID']>0)
        $schedule_ids[] = $enroll['UF_SHEDULE_ID'];
    $rows[] = $enroll;
}
if(count($user_ids)>0){
    foreach (User::getByIds(array_unique($user_ids), ['ID', 'NAME', 'EMAIL', 'LAST_NAME', 'PERSONAL_MOBILE', 'WORK_POSITION', 'UF_DEALER', 'UF_ROLE']) as $user){
        if(!$users[$user['ID']]['ID']>0) {
            $dealer_ids[] = $user['UF_DEALER'];
            if(check_full_array($user['UF_ROLE']))
                $role_ids = array_unique(array_merge($role_ids, $user['UF_ROLE']));
            $users[$user['ID']] = $user;
        }
    }
}
if(count($dealer_ids)>0){
    foreach (DealerHelper::getList(['ID'=>array_unique($dealer_ids)], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']) as $dealer){
        //dump($dealer);
        if(!$dealers[$dealer['ID']]['ID']>0) {
            $dealers[$dealer['ID']] = $dealer;
        }
    }
}
if(check_full_array($role_ids)){
    foreach (\Teaching\Roles::getRolesList(['ID'=>$role_ids], ['ID', 'NAME', 'CODE']) as $role){
        if(!$roles[$role['ID']]['ID']>0) {
            $roles[$role['ID']] = $role;
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
    if(check_full_array($row1['USER']['UF_ROLE'])){
        $string = '';
        foreach ($row1['USER']['UF_ROLE'] as $f => $r){
            if($f!=0)
                $string.=', ';
            $string.=$roles[$r]['NAME'];
        }
        $row1['USER']['UF_ROLE'] = $string;
    }
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
if($_REQUEST['date'])
    $selected_date = $_REQUEST['date'];
$selected_year = date('Y');
if($_REQUEST['year'])
    $selected_year = $_REQUEST['year'];
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase"><?= Loc::getMessage('H2_TITLE') ?></h2>
            <div class="table-block">
                <form class="report_generator" action="" method="get">
                    <span style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Дата</label>
                            <div class="select">
                                <select class="select2" name="date">
                                    <option value="0">Весь месяц</option>
                                    <?php for ($day=1; $day<=31; $day++){?>
                                        <option value="<?=$day?>"<?=$selected_date==$day?' selected':''?>><?=$day?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
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
                    </span>
                    <span style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="course_id" style="width: 100%;">
                                 <?php foreach (Courses::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']) as $course){?>
                                     <option value="<?=$course['ID']?>"<?=$course['ID']==(int)$_REQUEST['course_id']?' selected':''?>><?=$course['NAME']?></option>
                                 <?php }?>
                            </select>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Статус заявки</label>
                            <div class="select">
                                <select class="select2" name="status">
                                    <option value="all">Все заявки</option>
                                    <option value="not_approved"<?=$_REQUEST['status']=='not_approved'?' selected':''?>>Не подтверждена</option>
                                    <option value="approved"<?=$_REQUEST['status']=='approved'?' selected':''?>>Подтверждена</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%;">
                            <label for="">&nbsp;</label>
                            <button class="btn" style="height: 36px">Генерировать</button>
                        </div>
                    </span>
                </form>
                <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                    <thead class="thead-dark">
                    <tr>
                        <th><?= Loc::getMessage('DEALER_CODE') ?></th>
                        <th><?= Loc::getMessage('DEALER') ?></th>
                        <th>Город</th>
                        <th>Регионал ОП</th>
                        <th>Регионал ППО</th>
                        <th><?= Loc::getMessage('EMPLOYEE') ?></th>
                        <th>Роль</th>
                        <th>Должность</th>
                        <th>Email</th>
                        <th><?= Loc::getMessage('PHONE') ?></th>
                        <th><?= Loc::getMessage('COURSE') ?></th>
                        <th><?= Loc::getMessage('DATE_BEGIN') ?></th>
                        <th><?= Loc::getMessage('QUESTION') ?></th>
                        <th><?= Loc::getMessage('APPROVED') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $key => $row){
                        if($only_compls||$row['SCHEDULE']['PROPERTIES']['BEGIN_DATE']):?>
                            <tr style="height: 50px">
                                <td class="text-left"><?=$row['USER']['DEALER']['CODE']?></td>
                                <td class="text-left"><?=$row['USER']['DEALER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                <td><?=Dealer::getRegionalOP($row['USER']['DEALER']['ID'])?></td>
                                <td><?=Dealer::getRegionalPPO($row['USER']['DEALER']['ID'])?></td>
                                <td class="text-left"><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['UF_ROLE']?></td>
                                <td class="text-left"><?=$row['USER']['WORK_POSITION']?></td>
                                <td class="text-left"><?=$row['USER']['EMAIL']?></td>
                                <td class="text-left"><?=$row['USER']['PERSONAL_MOBILE']?></td>
                                <td class="text-left"><a href="/courses/<?=$row['COURSE']['CODE']?>/" target="_blank"><?=$row['COURSE']['NAME']?></a></td>
                                <td><?php if($row['SCHEDULE']['ID']>0){echo DateHelper::getHumanDate($row['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd.m.Y');} else { if($only_compls){echo DateHelper::getHumanDate($row['UF_DATE'], 'd.m.Y');}}?></td>
                                <td>
                                    <?php if(!empty($row['UF_REGISTER_ANSWER'])){
                                        $ans_arr = json_decode($row['UF_REGISTER_ANSWER'], true);
                                        if(!empty($ans_arr['question'])&&!empty($ans_arr['answer'])){
                                            echo $ans_arr['question'].': '.$ans_arr['answer'];
                                        }
                                        ?>
                                    <?php }?>
                                </td>
                                <td>
                                    <?php if($only_compls||$row['UF_IS_APPROVED']==1){?>
                                        <span class="status status--passed"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt=""></span><?= Loc::getMessage('APPR') ?></span>
                                    <?php } else {?>
                                        <span class="status status--passed"><?= Loc::getMessage('NOT_APPR') ?></span>
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
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Models\Employee;
use Teaching\Courses;
use Teaching\SheduleCourses;

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
$years = range((int)date('Y')-5, (int)date('Y')+1);
$request_string = [];
foreach ($_REQUEST as $key=>$value){
    $request_string[] = $key."=".$value;
}
$selected_month = date('m');
$selected_year = date('Y');
if($_REQUEST['month'])
    $selected_month = $_REQUEST['month'];
if($_REQUEST['year'])
    $selected_year = $_REQUEST['year'];
if(!empty($selected_month)||!empty($selected_year)){
    if(!empty($selected_year)&&!empty($selected_month)){
        $first_day_of_month = '01.'.$selected_month.'.'.$selected_year;
        $last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year).'.'.$selected_month.'.'.$selected_year;
        $filter = [
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        $filter_end = [
            '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
    }else{
        if(!empty($selected_year)&&empty($selected_month)){
            $first_day_of_month = '01.01.'.$selected_year;
            $last_day_of_month = '31.12.'.$selected_year;
            $filter = [
                '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
                '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
            ];
            $filter_end = [
                '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:01', "YYYY-MM-DD H:i:s"),
                '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
            ];
        } else {
            $sch = SheduleCourses::getIdsByTrainer(Employee::getTrainerId());
        }
    }
    if(!check_full_array($sch)) {
        $this_months_list = $temp_arr = SheduleCourses::collectInfo($filter, false);
        $this_months_end = SheduleCourses::collectInfo($filter_end, false);
        $sch = [];
        if ( check_full_array($this_months_list) ) {
            foreach ( $this_months_list as $item )
                $sch[] = $item['ID'];
        }
        if ( check_full_array($this_months_end) ) {
            foreach ($this_months_end as $item)
                if (!in_array($item['ID'], $sch))
                    $sch[] = $item['ID'];

        }
    }
} else {
    $sch = SheduleCourses::getIdsByTrainer(Employee::getTrainerId());
}
$schedules = [];
if(check_full_array($sch)>0) {
    $schedules = SheduleCourses::getArray(['ID' => $sch]);
    foreach ($schedules as $key => $schedule){
        $course = Courses::getById($schedule['PROPERTIES']['COURSE']);
        if(!$course['ID']>0) {
            unset($schedules[$key]);
            continue;
        }
        if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on') {
            if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']=='on'){
                $schedules[$key]['COURSE'] = $course;
            } else {
                if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on') {
                    if(Models\Course::isOP($course['ID'])){
                        $schedules[$key]['COURSE'] = $course;
                    } else {
                        unset($schedules[$key]);
                    }
                } else {
                    if(!Models\Course::isOP($course['ID'])){
                        $schedules[$key]['COURSE'] = $course;
                    } else {
                        unset($schedules[$key]);
                    }
                }
            }
        } else {
            $schedules[$key]['COURSE'] = $course;
        }
    }
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?= Loc::getMessage('TRAINER_DASHBOARD_TITLE') ?></h2>
            <div class="content-block  content-block--margin">
                <h3 class="h3 center"><?= Loc::getMessage('TRAINER_DASHBOARD_COURSE_LIST') ?></h3>
                <div class="table-block">
                    <form class="report_generator" action="" method="get" style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Месяц</label>
                            <div class="select">
                                <select class="select2" name="month">
                                    <option value="0">Не выбрано</option>
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
                                    <option value="0">Не выбрано</option>
                                    <?php foreach ($years as $year){?>
                                        <option value="<?=$year?>"<?=$year==$selected_year?' selected':''?>><?=$year?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">ОП/Сервис</label>
                            <div class="form-group" style="display: flex;margin-top: 14px;">
                                <div class="checkbox-item" style="padding-right: 20px">
                                    <input type="checkbox" id="op_servis_op" name="op_servis_op" <?=$_REQUEST['op_servis_op']=='on'?'checked':''?> style="display: none">
                                    <label for="op_servis_op" style="padding-left: 30px;">ОП</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="op_servis_servis" name="op_servis_servis" <?=$_REQUEST['op_servis_servis']=='on'?'checked':''?> style="display: none">
                                    <label for="op_servis_servis" style="padding-left: 30px;">Сервис</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%;">
                            <label for="">&nbsp;</label>
                            <button class="btn" style="height: 36px">Фильтровать</button>
                        </div>

                    </form>
                    <div class="form-group selectable" style="width: 100%;">
                        <label for="">&nbsp;</label>
                        <?php if(!check_full_array($request_string)){
                            $request_string[] = 'month='.$selected_month;
                            $request_string[] = 'year='.$selected_year;
                        }?>
                        <a href="/cabinet/trainer/report/?<?=implode("&", $request_string)?>" class="btn" style="height: 36px">Выгрузить отчет по участникам</a>
                    </div>
                    <?php if(check_full_array($schedules)){?>
                    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
                        <thead class="thead-dark">
                        <tr>
                            <th class="text-left"><?= Loc::getMessage('TRAINER_DASHBOARD_TABLE_COL_COURSE') ?></th>
                            <th class="text-left"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TABLE_COL_STATUS') ?></th>
                            <th class="text-left"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TABLE_COL_EMPLOYEES') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($schedules as $schedule){
                            $begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
                            $now_stmp = time();
                            $end_tmsmp = strtotime($schedule['PROPERTIES']['END_DATE'].' 23:59:59');
                            $text = $begin_tmstmp>$now_stmp?Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_NOT_STARTED'):Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_IN_PROCESS');
                            $text = $end_tmsmp<$now_stmp?Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_COMPLETED'):$text;?>
                            <tr>
                                <td>
                                    <a href="/cabinet/trainer/schedule/<?=$schedule['ID']?>/">
                                        <?=$schedule['NAME']?><br />
                                        <?php echo \Helpers\DateHelper::printDates($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE']);?>
                                    </a>
                                </td>
                                <td class="left"><?=$text?></td>
                                <td class="left"><?= SheduleCourses::getAllApproveExistsPlaces($schedule['ID'])?><?=(int)$schedule['PROPERTIES']['LIMIT']>0?'/'.(int)$schedule['PROPERTIES']['LIMIT']:''?></td>
                            </tr>
                        <?php }?>
                        </tbody>
                    </table>
                    <?php } else {?>
                        <h5 class="center">Согласно вашим фильтрам курсов не найдено</h5>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
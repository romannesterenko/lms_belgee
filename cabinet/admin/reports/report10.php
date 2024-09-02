<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Teaching\SheduleCourses;

global $USER;
$_REQUEST['report_id'] = 9999;
$month = $_REQUEST['month']??date('m');
$year = $_REQUEST['year']??date('Y');
?>

<?php
$rows = [];
$data = [];
$by_days = false;
//получим курсы доступные для роли
$first_day_of_month = '01.'.$month.'.'.$year;
$last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year).'.'.$month.'.'.$year;

$filter = [
    '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
    '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
];
$filter_end = [
    '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
    '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
];
if($_REQUEST['op_servis']=='op'||$_REQUEST['op_servis']=='ppo'){
    $filter['PROPERTY_COURSE'] = $filter_end['PROPERTY_COURSE'] = $_REQUEST['op_servis']=='op'?\Models\Course::getOPList(true):\Models\Course::getPPOList(true);
}
$this_months_list = $temp_arr = \Teaching\SheduleCourses::collectInfo($filter, $by_days);
$this_months_end = \Teaching\SheduleCourses::collectInfo($filter_end, $by_days);
if(check_full_array($this_months_end)){
    $this_months_end = array_reverse($this_months_end);
    foreach ($this_months_end as $day => $schedules) {
        if(!$this_months_list[$day]) {
            $temp_arr = array_reverse($temp_arr);
            $temp_arr[$day] = $schedules;
            $temp_arr = array_reverse($temp_arr);
        }
    }
}
$data = $temp_arr;
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
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase">Расписание модулей в расписании на месяц</h2>
            <div class="table-block">
                <form class="report_generator" action="" method="get">
                    <span style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">ОП / ППО</label>
                            <div class="select">
                                <select class="select2" name="op_servis">
                                    <option value="all">Все модули</option>
                                    <option value="op"<?=$_REQUEST['op_servis']=='op'?' selected':''?>>ОП</option>
                                    <option value="ppo"<?=$_REQUEST['op_servis']=='ppo'?' selected':''?>>ППО</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Месяц</label>
                            <div class="select">
                                <select class="select2" name="month">
                                    <?php foreach ($months as $id => $month_){?>
                                        <option value="<?=$id?>"<?=$month==$id?' selected':''?>><?=$month_?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Год</label>
                            <div class="select">
                                <select class="select2" name="year">
                                    <?php foreach ($years as $year_){?>
                                        <option value="<?=$year_?>"<?=$year==$year_?' selected':''?>><?=$year_?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable">
                            <label for="">&nbsp;</label>
                            <button class="btn" style="height: 36px">Генерировать</button>
                        </div>
                    </span>
                </form>
                <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                    <thead class="thead-dark">
                    <tr>
                        <th>Дата</th>
                        <th>Название модуля обучения</th>
                        <th>Количество сотрудников</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $key => $row){?>
                        <tr style="height: 50px">
                            <td class="text-left" data-id="<?=$row['ID']?>">
                                <?php $dates = [];
                                $dates[] = date('d.m.Y', strtotime($row['PROPERTIES']['BEGIN_DATE']));
                                $dates[] = date('d.m.Y', strtotime($row['PROPERTIES']['END_DATE']));
                                $dates = array_unique($dates);
                                ?>
                                <?=count($dates)>1? $dates[0]." - ".$dates[1]:$dates[0]?>
                            </td>
                            <td class="text-left"><?=$row['NAME']??$row['COURSE']['NAME']?></td>
                            <td class="text-center"><?=SheduleCourses::getExistsPlaces($row['ID'])?>/<?=(int)$row['PROPERTIES']['LIMIT']?></td>
                        </tr>
                    <?php
                    }?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
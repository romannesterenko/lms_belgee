<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Teaching\SheduleCourses;

global $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '10', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = 9999;
$month = $_REQUEST['month']??date('m');
$year = $_REQUEST['year']??date('Y');
?>

<?php

if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $_REQUEST['op_servis']='op';
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $_REQUEST['op_servis']='ppo';
    }
}


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
switch ($_REQUEST['direction']) {
    case 'A01':
        $filter['PROPERTY_COURSE'] = $filter_end['PROPERTY_COURSE'] = \Models\Course::getPPOList(true);
        break;
    case 'S01':
        $filter['PROPERTY_COURSE'] = $filter_end['PROPERTY_COURSE'] = \Models\Course::getOPList(true);
        break;
    case 'M01':
        $filter['PROPERTY_COURSE'] = $filter_end['PROPERTY_COURSE'] = \Models\Course::getMarketingList(true);
        break;
}
$this_months_list = $temp_arr = \Teaching\SheduleCourses::collectInfo($filter, $by_days);
$this_months_end = \Teaching\SheduleCourses::collectInfo($filter_end, $by_days);
$this_months_list_by_days = \Teaching\SheduleCourses::collectInfo($filter, true);
$this_months_end_by_days = \Teaching\SheduleCourses::collectInfo($filter_end, true);

//dump($this_months_list_by_days);
//dump($this_months_end_by_days);
$temp_all_data = [];
//$temp_end_data = [];
if (check_full_array($this_months_list_by_days)){
    foreach ($this_months_list_by_days as $date__ => $this_months_list_by_day) {
        foreach ($this_months_list_by_day as $this_months_list_by_day_schedule) {
            if (!$temp_all_data[$this_months_list_by_day_schedule['ID']])
                $temp_all_data[$this_months_list_by_day_schedule['ID']] = $this_months_list_by_day_schedule;
        }
    }
}
if (check_full_array($this_months_end_by_days)){
    foreach ($this_months_end_by_days as $date__ => $this_months_end_by_day) {
        foreach ($this_months_end_by_day as $this_months_end_by_day_schedule) {
            if (!$temp_all_data[$this_months_end_by_day_schedule['ID']])
                $temp_all_data[$this_months_end_by_day_schedule['ID']] = $this_months_end_by_day_schedule;
        }
    }
}

$temp_all_data = array_values($temp_all_data);

// Функция сортировки
usort($temp_all_data, function($a, $b) {
    return strtotime($a['PROPERTY_BEGIN_DATE_VALUE']) - strtotime($b['PROPERTY_BEGIN_DATE_VALUE']);
});
//dump($temp_all_data);
if(check_full_array($this_months_end)) {
    $this_months_end = array_reverse($this_months_end);
    foreach ($this_months_end as $day => $schedules) {
        if(!$this_months_list[$day]) {
            $temp_arr = array_reverse($temp_arr);
            $temp_arr[$day] = $schedules;
            $temp_arr = array_reverse($temp_arr);
        }
    }
}
$data = $temp_all_data;
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
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen"><span>Excel</span></button>
                    </div>
                </div>
                <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
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
                            <td class="text-center"><?=SheduleCourses::getExistsPlaces($row['ID'])?> из <?=(int)$row['PROPERTIES']['LIMIT']?></td>
                        </tr>
                    <?php
                    }?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
<?php

use Helpers\RequestHelper;
use Models\Course;
use Teaching\CourseCompletion;
use Teaching\Roles;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$_REQUEST['report_id'] = 9999;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

global $USER, $APPLICATION;
$directions = [
    'S01' => 'Отдел продаж',
    'A01' => 'Послепродажное обслуживание',
    'M01' => 'Маркетинг'
];
dump($_REQUEST);
$dealers_filter = ['!ID' => [360]];
if ($_REQUEST['show_none_active']!='Y')
    $dealers_filter = ['ACTIVE' => 'Y'];
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '21', 'request' => $_REQUEST]);
if(check_full_array($_REQUEST['dealer_names'])) {
    $dealers_filter['ID'] = $_REQUEST['dealer_names'];
}
$dealers = \Models\Dealer::getList($dealers_filter);
$data = [];

function getInfo($dealer, $direction_code, $start_date, $end_date)
{
    $men_days = 0;
    $sum = 0;
    $reserves = \Models\Reserve::get(['UF_DEALER_ID' => $dealer['ID'], 'UF_DIRECTION' => $direction_code, 'UF_IS_COMPLETE' => true]);
    $completion_ids = [];
    $schedule_ids = [];
    foreach ($reserves as $reserve) {
        $completion_ids[] = $reserve['UF_COMPLETION_ID'];
    }
    $completions = (new CourseCompletion)->get(['ID' => $completion_ids]);
    foreach($completions as $completion) {
        if ($completion['UF_SHEDULE_ID'] > 0 && !in_array($completion['UF_SHEDULE_ID'], $schedule_ids)){
            $schedule_ids[] = $completion['UF_SHEDULE_ID'];
        }
    }
    $new_completions = [];
    if(check_full_array($schedule_ids)) {
        $schedules = \Teaching\SheduleCourses::getArray(['ID' => $schedule_ids, '>=PROPERTY_END_DATE' => $start_date, '<=PROPERTY_END_DATE' => $end_date]);

        if(check_full_array($schedules)) {
            foreach($completions as $key => $completion) {
                if($completion['UF_SHEDULE_ID'] > 0 && check_full_array($schedules[$completion['UF_SHEDULE_ID']])) {

                    $new_completions[$completion['ID']] = $schedules[$completion['UF_SHEDULE_ID']];
                }
            }
        }
    }
    foreach ($reserves as $one_reserve) {
        if(check_full_array($new_completions[$one_reserve['UF_COMPLETION_ID']])){
            $sum+=$one_reserve['UF_PRICE'];
            $men_days+=(\Teaching\SheduleCourses::getDuration($new_completions[$one_reserve['UF_COMPLETION_ID']]["ID"]));
        }
    }
    return ['men_days' => $men_days, 'sum' => $sum];
}
$start_date = $_REQUEST['start_date']?$_REQUEST['start_date']." 00:00:00":'Y-m-01 00:00:00';
$end_date = $_REQUEST['end_date']?$_REQUEST['end_date']." 23:59:59":'Y-m-d 23:59:59';

foreach ($dealers as $dealer){
    $data[$dealer['ID']]['DEALER'] = $dealer;
    if($_REQUEST['direction'] == 'all'){
        foreach ($directions as $direction_code => $direction_title){
            $data[$dealer['ID']]['DIRECTIONS'][$direction_code] = getInfo($dealer, $direction_code, $start_date, $end_date);
        }
    } else {
        $data[$dealer['ID']]['DIRECTIONS'][$_REQUEST['direction']] = getInfo($dealer, $_REQUEST['direction'], $start_date, $end_date);
    }
}
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Отчет по реализации</h2>
                <div class="text-content text-content--long">
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К генератору</a>
                            </div>
                            <div class="btn-center">
                                <button class="btn" id="gen"><span>Excel</span></button>
                            </div>
                        </div>
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="vertical-align: middle" class="text-center">Направление</th>
                                    <th style="vertical-align: middle" class="text-center">Код</th>
                                    <th style="vertical-align: middle" class="text-center">Дилер</th>
                                    <th style="vertical-align: middle" class="text-center">Количество человекодней</th>
                                    <th style="vertical-align: middle" class="text-center">Сумма, рубли</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sss = 0;
                            foreach ($data as $key => $item){
                                foreach ($item['DIRECTIONS'] as $item_direction_code => $item_direction_info){
                                    $sss+=$item_direction_info['sum'];?>
                                    <tr>
                                        <td><?=$directions[$item_direction_code]?></td>
                                        <td><?=$item['DEALER']['CODE']?></td>
                                        <td><?=$item['DEALER']['NAME']?></td>
                                        <td><?=$item_direction_info['men_days']?></td>
                                        <td><?=number_format($item_direction_info['sum'], 0, '', ' ')?></td>
                                    </tr>
                                <?php }?>
                            <?php }
                            dump($sss);
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $(document).on('change', '.checkbox-item input[type="checkbox"]', function (){
                if($(this).attr('name')=='op_servis_op') {
                    $('label[for="op_servis_servis"]').trigger('click');
                }
                if($(this).attr('name')=='op_servis_servis') {
                    $('label[for="op_servis_op"]').trigger('click');
                }
            });
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
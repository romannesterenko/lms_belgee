<?php
use Bitrix\Main\Localization\Loc;
use Models\Dealer;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$_REQUEST['report_id'] = 999999;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$completions = new \Teaching\CourseCompletion();
$enrollments = new \Teaching\Enrollments();
$roless = \Models\Role::getAll(['ID', 'NAME', 'IBLOCK_SECTION_ID']);
$user_array = \Settings\Reports::generateTest(false, true);
$new_array = [];
$table_courses = [];
foreach ($user_array as $key_item => $temp_item){

    if($temp_item['NOT_ENROLLED']==1 || $temp_item['NOT_NEEDED'] == 1)
        continue;
    if( $temp_item['UF_IS_COMPLETE'] != 1 ) {
        if($temp_item['COURSE']['INFO']['PROPERTY_COURSE_TYPE_ENUM_ID']==6) {
            if(!check_full_array($temp_item['COURSE']['INFO']['PROPERTY_SCORM_VALUE'])&&$temp_item['COMPLETION_ID']>0){
                $test = current((new \Teaching\ProcessTest())->get(['UF_COMPLETION' => $temp_item['COMPLETION_ID']], ['UF_POINTS'])->getArray());
                if(check_full_array($test))
                    $user_array[$key_item]['UF_POINTS'] = (int)$test['UF_POINTS'];
            }
        }
    }
}

foreach ($user_array as $array){

    $new_array[$array['UF_USER_ID']][$array['UF_COURSE_ID']] = $array;
    if ($array['COURSE']['INFO']['ID']) {
        $table_courses[$array['COURSE']['INFO']['ID']] = $array['COURSE']['INFO'];
    }
}
usort($table_courses, function($a, $b){
    return ($a['PROPERTY_COURSE_FORMAT_ENUM_ID'] - $b['PROPERTY_COURSE_FORMAT_ENUM_ID']);
});
$tmp_arr = [];
foreach ($table_courses as $t_c){
    $tmp_arr[$t_c['ID']] = $t_c;
}
$table_courses = $tmp_arr; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen8"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                        <thead class="thead-dark">
                        <tr>
                            <th>Код дилера</th>
                            <th>Название дилера</th>
                            <?php
                            if ($_REQUEST['direction']=='all'){?>
                                <th>Регионал ОП</th>
                                <th>Регионал ППО</th>
                                <th>Регионал Маркетинг</th>
                            <?php } else {
                                switch ($_REQUEST['direction']) {
                                    case "S01":
                                        echo "<th>Регионал ОП</th>";
                                        break;
                                    case "A01":
                                        echo "<th>Регионал ППО</th>";
                                        break;
                                    case "M01":
                                        echo "<th>Регионал Маркетинг</th>";
                                        break;
                                }
                            }?>
                            <th>ФИО</th>
                            <th>Уровень</th>
                            <th>Дата начала работы</th>
                            <th>Роль</th>
                            <?php foreach ($table_courses as $table_course){?>
                                <th><?=$table_course['NAME']?></th>
                                <th>Дата</th>
                            <?php }?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_array as $item){
                            $first_item = current($item);
                            ?>
                            <tr>
                                <td><?=$first_item['DEALER']['CODE']?></td>
                                <td><?=$first_item['DEALER']['NAME']?></td>
                                <?php
                                if ($_REQUEST['direction']=='all') {?>
                                    <td><?=Dealer::getRegionalOP($first_item['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalPPO($first_item['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalMarketing($first_item['DEALER']['ID'])?></td>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case "S01":
                                            echo "<td>".Dealer::getRegionalOP($first_item['DEALER']['ID'])."</td>";
                                            break;
                                        case "A01":
                                            echo "<td>".Dealer::getRegionalPPO($first_item['DEALER']['ID'])."</td>";
                                            break;
                                        case "M01":
                                            echo "<td>".Dealer::getRegionalMarketing($first_item['DEALER']['ID'])."</td>";
                                            break;
                                    }
                                }?>
                                <td>
                                    <?=$first_item['NAME']?> <?=$first_item['LAST_NAME']?>
                                    <?php if($_REQUEST['need_show_ids'] == 'Y') {?>
                                        <br />ID: <?=$first_item['ID']?>
                                        <br /><?=$first_item['EMAIL']?>
                                    <?php }?>
                                </td>
                                <td><?=$first_item['UF_USER_RATING']?></td>
                                <td><?=$first_item['UF_WORK_START_DATE']?></td>
                                <td><?php foreach ($first_item['UF_ROLE'] as $r){?><?=$roless[$r]['NAME']?><br /><?php }?></td>
                                <?php foreach ($table_courses as $table_course) {
                                    if($item[$table_course['ID']]['UF_IS_COMPLETE']==1){?>
                                        <td>Пройден</td>
                                        <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                    <?php } else {
                                        if ($item[$table_course['ID']]['NOT_NEEDED']){?>
                                            <td>-</td>
                                            <td>-</td>
                                            <?php /*<td>-</td>*/?>
                                        <?php } elseif ($item[$table_course['ID']]['NOT_ENROLLED']){?>
                                            <td>Не записывался</td>
                                            <td>-</td>
                                            <?php /*<td>-</td>*/?>
                                        <?php } else {
                                            if(!empty($item[$table_course['ID']]['UF_DATE'])&&time()<$item[$table_course['ID']]['UF_DATE']->getTimestamp()){?>
                                                <td>Записан</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <?php /*<td>-</td>*/?>
                                            <?php } else {?>
                                                <td>Не пройден</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <?php /*<td><?=(int)$item[$table_course['ID']]['UF_POINTS']?><?=$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']>0?"/".$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']:""?></td>*/?>
                                            <?php }
                                        }?>
                                    <?php }
                                    /*} else {*/?><!--
                                            <td>Не записывался</td>
                                            <td></td>
                                            <td></td>
                                        --><?php /*}*/?>

                                <?php }?>
                            </tr>
                        <?php }?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <style>
        .container{
            max-width: none;
        }
    </style>
<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
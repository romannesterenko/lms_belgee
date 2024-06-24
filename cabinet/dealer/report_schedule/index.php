<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$_REQUEST['report_id'] = 999999;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$start_date = date('Y-m-d 00:00:01');
$end_date = date('Y-m-d 23:59:59', strtotime('+ 4 months'));
$filter = [
    '>PROPERTY_BEGIN_DATE' => $start_date,
    '<PROPERTY_BEGIN_DATE' => $end_date,
];
if(!empty($_REQUEST['direction']) && $_REQUEST['direction']!='all'){
    switch ($_REQUEST['direction']) {
        case "S01":
            $courses = \Models\Course::getOPList(true);
            break;
        case "A01":
            $courses = \Models\Course::getPPOList(true);
            break;
        case "M01":
            $courses = \Models\Course::getMarketingList(true);
            break;
    }
    $filter['PROPERTY_COURSE'] = $courses;
}
$schedules = \Teaching\SheduleCourses::getArray($filter);
?>

    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <div class="text-content text-content--long">
                    <h2 class="h2 center">Расписания</h2>
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <form action="" style="display:flex; width: 100%">
                        <div class="select">
                            <select class="select2" name="direction">
                                <option value="all">Направление</option>
                                <option value="S01"<?=$_REQUEST['direction'] == 'S01'?" selected":""?>>ОП</option>
                                <option value="A01"<?=$_REQUEST['direction'] == 'A01'?" selected":""?>>ППО</option>
                                <option value="M01"<?=$_REQUEST['direction'] == 'M01'?" selected":""?>>Маркетинг</option>
                            </select>
                        </div>
                        <div class="btn-center">
                            <button type="submit" class="btn">Фильтровать</button>
                        </div>
                        </form>
                        <div class="btn-center">
                            <button id="gen" class="btn">Excel</button>
                        </div>
                    </div>
                    <div class="table-block">

                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="vertical-align: middle" class="text-left">Даты</th>
                                    <th style="vertical-align: middle" class="text-left">Наименование курса</th>
                                    <th style="vertical-align: middle" class="text-left">Количество свободных мест / Всего мест</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule){?>
                                    <tr>
                                        <td style="text-align: left; "><?=\Helpers\DateHelper::printDates($schedule['PROPERTY_BEGIN_DATE_VALUE'], $schedule['PROPERTY_END_DATE_VALUE'])?></td>
                                        <td style="text-align: left"><?=$schedule['NAME']?></td>
                                        <td style="text-align: center"><?=\Teaching\SheduleCourses::getFreePlacesBySchedule($schedule['ID'])?> / <?=$schedule['PROPERTY_LIMIT_VALUE']?></td>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
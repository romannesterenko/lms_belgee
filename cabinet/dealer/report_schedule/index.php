<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$start_date = date('Y-m-d 00:00:01');
$end_date = date('Y-m-d 23:59:59', strtotime('+ 4 months'));
$schedules = \Teaching\SheduleCourses::getArray([
    '>PROPERTY_BEGIN_DATE' => $start_date,
    '<PROPERTY_BEGIN_DATE' => $end_date,
]);
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
                        <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
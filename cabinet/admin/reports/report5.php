<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
$settings = \Settings\Reports::getMenDaysPerfReport();
$_REQUEST['report_id'] = 1;
$select = \Teaching\Courses::getDirectionsList();
$select[] = [
    'UF_NAME' => 'OTHERS',
    'UF_XML_ID' => false,
];
$schedules_filter = [
    'ACTIVE'>'Y',
    '!PROPERTY_BEGIN_DATE'=>false,
    '!PROPERTY_COURSE'=>false
];
$no_fnd_by_filter = false;
if(!empty($_REQUEST['dir'])&&$_REQUEST['dir']!=0){
    $no_fnd_by_filter = true;
    $direction = $_REQUEST['dir']=='OTHERS'?false:strtoupper($_REQUEST['dir']);
    $cat_courses = \Teaching\Courses::getByDirection($direction);
    foreach ($cat_courses as $cat_course){
        $no_fnd_by_filter = false;
        $schedules_filter['PROPERTY_COURSE'][] = $cat_course['ID'];
    }
}
if($_REQUEST['start_date']){
    $schedules_filter['>PROPERTY_BEGIN_DATE'] = $_REQUEST['start_date'].' 00:00:01';
}
if($_REQUEST['end_date']){
    $schedules_filter['<PROPERTY_END_DATE'] = $_REQUEST['end_date'].' 23:59:59';
}
$schedules = $no_fnd_by_filter?[]: \Teaching\SheduleCourses::getArray(
        $schedules_filter,
        [
            'ID',
            'PROPERTY_BEGIN_DATE',
            'PROPERTY_END_DATE',
            'PROPERTY_COURSE',
            'PROPERTY_LIMIT',
        ]
);
$course_ids = [];
foreach($schedules as $schedule){
    $course_ids[] = $schedule['PROPERTY_COURSE_VALUE'];
}
$courses = \Teaching\Courses::getList(['ID' => array_unique($course_ids)], ['NAME', 'PROPERTY_CITY', 'PROPERTY_COURSE_CATEGORY']);
$completions = new \Teaching\CourseCompletion();
foreach($schedules as $key => &$schedule){
    $schedule['COUNT_DATES'] = \Helpers\DateHelper::getIntervalArray(
            \Helpers\DateHelper::getFormatted($schedule['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y'),
            $schedule['PROPERTY_END_DATE_VALUE']
    );
    if($courses[$schedule['PROPERTY_COURSE_VALUE']]['ID']>0) {
        $schedule['COURSE'] = $courses[$schedule['PROPERTY_COURSE_VALUE']];
    }else
        unset($schedules[$key]);
    $schedule['TR_DAYS'] = count($schedule['COUNT_DATES']);
    $schedule['TRAINED'] = count($completions->getCompletedItemsBySchedule($schedule['ID']));
    $schedule['MEN_DAYS_PLAN'] = $schedule['TR_DAYS']*(int)$schedule['PROPERTY_LIMIT_VALUE'];
    $schedule['MEN_DAYS_FACT'] = $schedule['TR_DAYS']*$schedule['TRAINED'];
    $schedule['PERFOMANCE'] = $schedule['MEN_DAYS_PLAN']>0?$schedule['MEN_DAYS_FACT']/$schedule['MEN_DAYS_PLAN']*100:0;
}
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
                <div class="text-content text-content--long">
                    <h2 class="h2 center"><?= Loc::getMessage('TITLE') ?></h2>
                    <?php if($USER->IsAdmin()){?>
                        <form style="display: flex; justify-content: space-between">
                            <div class="form-group" style="display: flex;">
                                <div class="form-group mr-20">
                                    <label for=""><?= Loc::getMessage('CATEGORY') ?></label>
                                    <div class="select">
                                        <select class="select2" name="dir">
                                            <option value="0" style="padding-left: 5px"><?= Loc::getMessage('ALL_CATEGORIES') ?></option>
                                            <?php foreach ($select as $dir){?>
                                                <option value="<?=$dir['UF_NAME']?>" <?=$dir['UF_NAME']==$_REQUEST['dir']?'selected':''?>><?=$dir['UF_NAME']?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mr-20">
                                    <label for=""><?= Loc::getMessage('BEGIN_DATE') ?></label>
                                    <input type="date" value="<?=$_REQUEST['start_date']?>" name="start_date">
                                </div>
                                <div class="form-group mr-20">
                                    <label for=""><?= Loc::getMessage('END_DATE') ?></label>
                                    <input type="date" value="<?=$_REQUEST['start_date']?>" name="end_date">
                                </div>
                            </div>
                            <div class="form-group" style="display: flex; padding-top: 1rem;">
                                <div class="btn-center">
                                    <button class="btn"><?= Loc::getMessage('SUBMIT') ?></button>
                                </div>
                                <div class="btn-center">
                                    <a role="button" href="<?=$APPLICATION->GetCurPage()?>" class="btn"><?= Loc::getMessage('RESET') ?></a>
                                </div>
                            </div>
                        </form>
                    <?php }?>
                    <div class="table-block">
                        <div class="btns"></div>
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Дата</th>
                                    <th class="text-center">Тренинг</th>
                                    <th class="text-center">Город</th>
                                    <th class="text-center">Дней</th>
                                    <th class="text-center">Участников (план)</th>
                                    <th class="text-center">Дилеров (факт)</th>
                                    <th class="text-center">Участников (факт)</th>
                                    <th class="text-center">Человекодни (план)</th>
                                    <th class="text-center">Человекодни (факт)</th>
                                    <th class="text-center">Продуктивность, %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule){?>
                                    <tr>
                                        <td><?=$schedule['COURSE']['PROPERTY_COURSE_CATEGORY_VALUE']?></td>
                                        <td><?=\Helpers\DateHelper::getFormatted($schedule['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')?> - <?=\Helpers\DateHelper::getFormatted($schedule['PROPERTY_END_DATE_VALUE'], 'd.m.Y')?></td>
                                        <td class="text-left"><a href="/cabinet/diller/reports/course_completions/<?=$schedule['COURSE']['ID']?>/" target="_blank"><?=$schedule['COURSE']['NAME']?></a></td>
                                        <td><?=$schedule['COURSE']['PROPERTY_CITY_VALUE']?></td>
                                        <td><?=$schedule['TR_DAYS']?></td>
                                        <td><?=(int)$schedule['PROPERTY_LIMIT_VALUE']?></td>
                                        <td><?=(int)$schedule['PROPERTY_LIMIT_VALUE']?></td>
                                        <td><?=$schedule['TRAINED']?></td>
                                        <td><?=$schedule['MEN_DAYS_PLAN']?></td>
                                        <td><?=$schedule['MEN_DAYS_FACT']?></td>
                                        <td><?=round($schedule['PERFOMANCE'], 1)?>%</td>
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

<?php
global $APPLICATION, $USER;

use Bitrix\Main\Localization\Loc;
use Models\Employee;
use Teaching\Courses;
use Teaching\SheduleCourses;

$courses_by_trainer = Courses::getIdsByTrainer(Employee::getTrainerId());
$schedules = [];
if(count($courses_by_trainer)>0) {
//    $schedules = SheduleCourses::getArray(['PROPERTY_COURSE' => $courses_by_trainer]);
    $schedules = SheduleCourses::getArray([]);
    foreach ($schedules as $key => $schedule){
        $schedules[$key]['COURSE'] = Courses::getById($schedule['PROPERTIES']['COURSE']);
    }
}
?>
<h2 class="h2"><?= Loc::getMessage('TRAINER_DASHBOARD_TITLE') ?></h2>
<div class="content-block  content-block--margin">
    <h3 class="h3 center"><?= Loc::getMessage('TRAINER_DASHBOARD_COURSE_LIST') ?></h3>
    <div class="table-block">
        <?php /*
        <form class="training-filter">
            <div class="training-filter__item filter-input">
                <label for=""><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_DATES') ?></label>
                <div class="calendar-range">
                    <input type="text" name="dates" value="<?=$_REQUEST['dates']?>" class="datepicker-here" id="rangecalendar" data-range="true" data-multiple-dates-separator=" - " placeholder="Выберите даты">
                </div>
            </div>
            <div class="training-filter__item filter-select">
                <div class="select-block ">
                    <label for=""><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS') ?></label>
                    <div class="select select--custom2">
                        <select class="select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true" name="status">
                            <option value="all"<?=$_REQUEST['status']=='all'?' selected':''?>><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_ALL') ?></option>
                            <option value="not_started"<?=$_REQUEST['status']=='not_started'?' selected':''?>><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_NOT_STARTED') ?></option>
                            <option value="started"<?=$_REQUEST['status']=='started'?' selected':''?>><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_IN_PROCESS') ?></option>
                            <option value="completed"<?=$_REQUEST['status']=='completed'?' selected':''?>><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_COMPLETED') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="training-filter__item filter-btn mt-20" >
                <button role="button" type="submit" class="btn btn--md"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_SUBMIT_BUTTON') ?></button>
            </div>
        </form> */?>
        <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
            <thead class="thead-dark">
                <tr>
                    <th class="text-left"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TABLE_COL_DATE') ?></th>
                    <th class="text-left"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TABLE_COL_STATUS') ?></th>
                    <th class="text-left"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TABLE_COL_EMPLOYEES') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($schedules as $schedule){
                $begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
                $now_stmp = time();
                $end_tmsmp = strtotime($schedule['PROPERTIES']['END_DATE']);
                $text = $begin_tmstmp>$now_stmp?Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_NOT_STARTED'):Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_IN_PROCESS');
                $text = $end_tmsmp<$now_stmp?Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_COMPLETED'):$text;?>
                <tr>
                    <td><a href="/cabinet/trainer/schedule/<?=$schedule['ID']?>/"><?=$schedule['COURSE']['NAME']?><br /><?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'])?> - <?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'])?></a></td>
                    <td class="left"><?=$text?></td>
                    <td class="left"><?= SheduleCourses::getExistsPlaces($schedule['ID'])?>/<?=$schedule['PROPERTIES']['LIMIT']?></td>
                </tr>
            <?php }?>
            </tbody>
        </table>

    </div>
</div>

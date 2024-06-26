<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Teaching\Courses;
use Teaching\SheduleCourses;

$course = Courses::getById($_REQUEST['id']);
if($course===[]){
    Helpers\PageHelper::set404(Loc::getMessage('COURSE_NOT_FOUND'));
    die();
}
$schedules = SheduleCourses::getArray(['PROPERTY_COURSE' => $course['ID']]);
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?= Loc::getMessage('TRAINER_SCHEDULES_LIST_TITLE', ['#COURSE_NAME#' => $course['NAME']]) ?></h2>
        <div class="table-block">
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
            </form>
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
                        $text = $end_tmsmp<$now_stmp?Loc::getMessage('TRAINER_SCHEDULES_LIST_FILTER_STATUS_COMPLETED'):$text;
                        ?>
                        <tr>
                            <td><a href="/cabinet/trainer/schedule/<?=$schedule['ID']?>/"><?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'])?> - <?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'])?></a></td>
                            <td class="left"><?=$text?></td>
                            <td class="left"><?= SheduleCourses::getExistsPlaces($schedule['ID'])?>/<?=$schedule['PROPERTIES']['LIMIT']?></td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>

        </div>

        <?php /*<div class="pagination">
            <div class="pagination__nav">
                <a href="" class="prev"><span class="icon icon-arrow-link"></span>предыдущая</a>
                <a href="" class="next">следующая<span class="icon icon-arrow-link"></span></a>
            </div>
            <div class="pagination__pages">
                <span><a href="" class="active">1</a></span>
                <span><a href="">2</a></span>
                <span><a href="">3</a></span>
                <span><a href="">4</a></span>
                <span><a href="">5</a></span>
                <span><a href="">6</a></span>
                <span><a href="">7</a></span>
                <span><a href="">8</a></span>
            </div>
        </div>*/?>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>



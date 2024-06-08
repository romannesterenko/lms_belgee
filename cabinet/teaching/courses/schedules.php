<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

if((int)$_REQUEST['id']<=0) {
    Helpers\PageHelper::set404("Курс не найден");
    die();
}
$course = \Models\Course::find((int)$_REQUEST['id'], ['NAME']);
if(!check_full_array($course)){
    Helpers\PageHelper::set404("Курс не найден");
    die();
}
$users = \Models\User::getEmployeesByAdmin();
$schedules = [];
$counts = [];
if(check_full_array($users)){
    $user_ids = [];
    foreach ($users as $user){
        $user_ids[] = $user['ID'];
    }
    $completions = new \Teaching\CourseCompletion();
    $ddd = $completions->getListByCourseAndUser($user_ids, $course['ID']);
    if(check_full_array($ddd)){
        $schedule_ids = [];
        foreach ($ddd as $item){
            if(!in_array($item['UF_SHEDULE_ID'], $schedule_ids))
                $schedule_ids[] = $item['UF_SHEDULE_ID'];
        }
    }
    if(check_full_array($schedule_ids))
        $schedules = \Teaching\SheduleCourses::getById($schedule_ids);

    foreach ($ddd as $item)
        $counts[$item['UF_SHEDULE_ID']][] = $schedules[$item['UF_SHEDULE_ID']];
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2">Расписания курса "<?=$course["NAME"]?>"</h2>
            <div class="content-block  content-block--margin">
                <h3 class="h3 center"><?= Loc::getMessage('TRAINER_DASHBOARD_COURSE_LIST') ?></h3>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left">Наименование</th>
                                <th class="text-left">Начало</th>
                                <th class="text-left">Конец</th>
                                <th class="text-left">Участников ДЦ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(check_full_array($schedules)){?>
                                <?php foreach ($schedules as $schedule){?>
                                    <tr>
                                        <td class="text-left">
                                            <a href="/cabinet/teaching/schedule/<?=$schedule['ID']?>/"><?=$schedule['NAME']?></a>
                                        </td>
                                        <td class="text-left">
                                            <?=$schedule['PROPERTY_BEGIN_DATE_VALUE']?>
                                        </td>
                                        <td class="text-left">
                                            <?=$schedule['PROPERTY_END_DATE_VALUE']?>
                                        </td>
                                        <td class="text-left">
                                            <?=check_full_array($counts)?count($counts[$schedule['ID']]):0?>
                                        </td>
                                    </tr>
                                <?php }?>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
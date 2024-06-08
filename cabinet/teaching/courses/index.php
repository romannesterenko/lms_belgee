<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

$courses_by_TA = \Models\Course::getByTeachingAdmin();
$users = \Models\User::getEmployeesByAdmin();
if(check_full_array($users)){
    $user_ids = [];
    foreach ($users as $user){
        $user_ids[] = $user['ID'];
    }
    $completions = new \Teaching\CourseCompletion();
    $ddd = $completions->getListByCourseAndUser($user_ids, array_keys($courses_by_TA));
    if(check_full_array($ddd)){
        $course_ids = [];
        foreach ($ddd as $item){
            if(!in_array($item['UF_COURSE_ID'], $course_ids))
                $course_ids[] = $item['UF_COURSE_ID'];
        }
    }
    $courses_by_TA = \Teaching\Courses::getList(['ID' => $course_ids], ['ID', 'NAME', 'PROPERTY_COURSE_TYPE']);
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?= Loc::getMessage('TRAINER_DASHBOARD_TITLE') ?></h2>
            <div class="content-block  content-block--margin">
                <h3 class="h3 center"><?= Loc::getMessage('TRAINER_DASHBOARD_COURSE_LIST') ?></h3>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left"><?= Loc::getMessage('TRAINER_DASHBOARD_COURSE_LIST') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses_by_TA as $course){?>
                                <tr>
                                    <td class="text-left"><a href="<?='/cabinet/teaching/course/'.$course['ID'].'/'?>"><?=$course['NAME']?></a></td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?php

use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
use Helpers\UserHelper;
use Polls\ProcessPoll;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\Roles;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER, $notifications_main_filter, $my_courses_filter, $needed_courses_filter, $courses_for_role_filter;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
//one more1234asd123ddфывфыв
if(check_full_array(UserHelper::getRoleByCurrentUser())) {
        $need_op_admin = false;
        $need_ppo_admin = false;
        $need_marketing_admin = false;
        foreach (UserHelper::getRoleByCurrentUser() as $role_id) {
            if(!$need_op_admin) {
                $need_op_admin = \Models\Role::isOP($role_id);
            }
            if(!$need_ppo_admin) {
                $need_ppo_admin = \Models\Role::isPPO($role_id);
            }
            if(!$need_marketing_admin) {
                $need_marketing_admin = \Models\Role::isMarketing($role_id);
            }
        }
        if($need_op_admin||$need_ppo_admin||$need_marketing_admin) {?>
            <div class="content-block mb-10">
                <?php if($need_op_admin) {
                    $admin_op = UserHelper::getOPAdminByUser();?>
                    <p style="font-weight: bold">Администраторы ОП:</p>
                    <?php if(check_full_array($admin_op)){ ?>
                        <?php foreach ($admin_op as $op_admin){?>
                            <p><?=$op_admin['LAST_NAME']?> <?=$op_admin['NAME']?>, <a href="mailto:<?=$op_admin['EMAIL']?>"><?=$op_admin['EMAIL']?></a>, <a href="tel:<?=$op_admin['PERSONAL_MOBILE']??$op_admin['PERSONAL_PHONE']?>"><?=$op_admin['PERSONAL_MOBILE']??$op_admin['PERSONAL_PHONE']?></a></p>
                        <?php }?>
                    <?php } else {?>
                        <p>Нет данных</p>
                    <?php }?>
                <?php }?>
                <?php if($need_ppo_admin) {
                    $admin = UserHelper::getPPOAdminByUser();?>
                    <p style="font-weight: bold; margin-top: 10px">Администраторы ППО</p>
                    <?php if(check_full_array($admin)){ ?>
                        <?php foreach ($admin as $ppo_admin){?>
                            <p><?=$ppo_admin['LAST_NAME']?> <?=$ppo_admin['NAME']?>, <a href="mailto:<?=$ppo_admin['EMAIL']?>"><?=$ppo_admin['EMAIL']?></a>, <a href="tel:<?=$ppo_admin['PERSONAL_MOBILE']??$ppo_admin['PERSONAL_PHONE']?>"><?=$ppo_admin['PERSONAL_MOBILE']??$ppo_admin['PERSONAL_PHONE']?></a></p>
                        <?php }?>
                    <?php } else {?>
                        <p>Нет данных</p>
                    <?php }?>
                <?php }?>
                <?php if($need_marketing_admin) {
                    $admins = UserHelper::getMarketingAdminByUser();?>
                    <p style="font-weight: bold; margin-top: 10px">Администраторы Маркетинг</p>
                    <?php if(check_full_array($admins)){ ?>
                        <?php foreach ($admins as $marketing_admin){?>
                            <p><?=$marketing_admin['LAST_NAME']?> <?=$marketing_admin['NAME']?>, <a href="mailto:<?=$marketing_admin['EMAIL']?>"><?=$marketing_admin['EMAIL']?></a>, <a href="tel:<?=$marketing_admin['PERSONAL_MOBILE']??$marketing_admin['PERSONAL_PHONE']?>"><?=$marketing_admin['PERSONAL_MOBILE']??$marketing_admin['PERSONAL_PHONE']?></a></p>
                        <?php }?>
                    <?php } else {?>
                        <p>Нет данных</p>
                    <?php }?>
                <?php }?>
            </div>
        <?php }
}

if(check_full_array(Roles::getById(UserHelper::getRoleByCurrentUser()))){?>
    <h2><?= Loc::getMessage('MY_ROLES') ?></h2>
    <div class="content-block mb-10">
        <?php if(check_full_array(Roles::getById(UserHelper::getRoleByCurrentUser()))) {?>
            <p><?=implode(', ', Roles::getById(UserHelper::getRoleByCurrentUser()))?></p>
        <?php } else {?>
            <p>Нет данных</p>
        <?php }?>
    </div>
<?php }?>

<?php
global $pollsFilter;
$pollsProcess = new ProcessPoll();
$complete_polls = $pollsProcess->getCompletePollIdsByCurrentUser();
$pollsFilter['UF_ROLES'] = Roles::getByUser();
if(count($complete_polls)>0)
    $pollsFilter['!ID'] = $complete_polls;
if(check_full_array($pollsFilter['UF_ROLES'])) {
    $APPLICATION->IncludeComponent("bitrix:catalog.section.list", "polls_main", array(
        "VIEW_MODE" => "TEXT",
        "SHOW_PARENT_NAME" => "Y",
        "IBLOCK_ID" => IBlockHelper::getPollsIBlock(),
        "SECTION_ID" => false,
        "SECTION_CODE" => "",
        "SECTION_URL" => "",
        "COUNT_ELEMENTS" => "Y",
        "TOP_DEPTH" => "2",
        "SECTION_FIELDS" => "",
        "SECTION_USER_FIELDS" => "",
        "ADD_SECTIONS_CHAIN" => "Y",
        "CACHE_TYPE" => "N",
        "CACHE_TIME" => "36000000",
        "CACHE_NOTES" => "",
        "CACHE_GROUPS" => "Y",
        "FILTER_NAME" => "pollsFilter",
    ),
        false
    );
}
?>
<?php if(\Helpers\UserHelper::isLocalAdmin()){?>
    <h2 class="h2" style="margin-top: 35px;">Записанные сотрудники</h2>
    <div class="content-block  content-block--margin">
        <?php $users = \Models\Employee::getActiveEmployeesByDealerAdmin();
        $new_users_array = [];
        $ids = [];
        foreach ($users as $user){
            $new_users_array[$user['ID']] = $user;
            $ids[] = $user['ID'];
        }
        $enrolls = (new \Teaching\Enrollments())->get(['UF_USER_ID' => $ids, '>UF_DATE'=>date('d.m.Y'), 'UF_IS_APPROVED' => 1], ['*'], ['UF_DATE' => 'ASC']);
        if(check_full_array($enrolls)){?>
            <div class="table-block">
                <table class="table table-bordered" id="table-report" style="padding-top: 25px">

                    <thead class="thead-dark">
                    <tr>
                        <th style="vertical-align: middle;">ФИО</th>
                        <th style="vertical-align: middle;">Курс</th>
                        <th style="vertical-align: middle;">Дата</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($enrolls as $enroll){
                        $course = \Models\Course::find($enroll['UF_COURSE_ID'], ['ID', 'NAME']);
                        $shedule = $enroll['UF_SHEDULE_ID']>0?current(\Teaching\SheduleCourses::getById($enroll['UF_SHEDULE_ID'])):[];?>
                        <tr>
                            <td style="vertical-align: middle;"><?=$new_users_array[$enroll['UF_USER_ID']]['NAME']?> <?=$new_users_array[$enroll['UF_USER_ID']]['LAST_NAME']?></td>
                            <td style="vertical-align: middle; text-align: left"><?=$shedule['NAME']??$course['NAME']?></td>
                            <td style="vertical-align: middle;"><?=$enroll['UF_DATE']?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
        <?php } else {?>
            <p>Записей нет</p>
        <?php }?>

    </div>
<?php }?>
<?php $notifications_main_filter['UF_USER_ID'] = $USER->GetID();
$notifications_main_filter['UF_IS_READ'] = false;
$APPLICATION->IncludeComponent("bitrix:highloadblock.list", "notifications_main", array(
        "BLOCK_ID" => "3",
        "CHECK_PERMISSIONS" => "Y",
        "DETAIL_URL" => "",
        "FILTER_NAME" => "notifications_main_filter",
        "PAGEN_ID" => "page",
        "ROWS_PER_PAGE" => "2"
    )
);
$enrollments = new Enrollments();
$my_courses_filter["ID"] = $enrollments->getMyCourseIds();
foreach ((new \Teaching\CourseCompletion())->get(["UF_USER_ID" => $USER->GetID(), 'UF_IS_COMPLETE' => false, 'UF_DIDNT_COM' => false, 'UF_FAILED' => false]) as $enroll) {
    if($enroll['UF_SHEDULE_ID'] > 0)
        $my_courses_filter["ID"][] = $enroll['UF_COURSE_ID'];
}
if (count($my_courses_filter["ID"]) > 0) {
    $APPLICATION->IncludeComponent("bitrix:news.list", "main_page_courses", array(
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "AJAX_MODE" => "Y",
            "IBLOCK_TYPE" => "timetable",
            "IBLOCK_ID" => IBlockHelper::getCoursesIBlock(),
            "NEWS_COUNT" => "10",
            "SORT_BY1" => "ACTIVE_FROM",
            "SORT_ORDER1" => "DESC",
            "SORT_BY2" => "SORT",
            "SORT_ORDER2" => "ASC",
            "FILTER_NAME" => "my_courses_filter",
            "FIELD_CODE" => array("ID"),
            "PROPERTY_CODE" => array("BEGIN_DATE"),
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "SET_TITLE" => "N",
            "SET_BROWSER_TITLE" => "N",
            "SET_META_KEYWORDS" => "Y",
            "SET_META_DESCRIPTION" => "Y",
            "SET_LAST_MODIFIED" => "Y",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
            "ADD_SECTIONS_CHAIN" => "Y",
            "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "INCLUDE_SUBSECTIONS" => "Y",
            "CACHE_TYPE" => "N",
            "CACHE_TIME" => "3600",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "Y",
            "DISPLAY_TOP_PAGER" => "Y",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_SHOW_ALWAYS" => "Y",
            "PAGER_TEMPLATE" => "",
            "PAGER_DESC_NUMBERING" => "Y",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "Y",
            "PAGER_BASE_LINK_ENABLE" => "Y",
            "SET_STATUS_404" => "Y",
            "SHOW_404" => "Y",
            "MESSAGE_404" => "",
            "PAGER_BASE_LINK" => "",
            "PAGER_PARAMS_NAME" => "arrPager",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_ADDITIONAL" => ""
        )
    );
}
$ids = Roles::GetRequiredCourseIdsByUser();
$exist_ids = [];
foreach ($enrollments->getListByUser($USER->GetID(), false) as $enroll) {
    $exist_ids[] = $enroll['UF_COURSE_ID'];
}
$needed_courses_filter["ID"] = count($exist_ids) > 0 ? array_diff($ids, $exist_ids) : $ids;
if (count($needed_courses_filter["ID"]) > 0) {
    $APPLICATION->IncludeComponent("bitrix:news.list", "main_page_needed_courses", array(
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "AJAX_MODE" => "Y",
            "IBLOCK_ID" => IBlockHelper::getCoursesIBlock(),
            "NEWS_COUNT" => "10",
            "SORT_BY1" => "ACTIVE_FROM",
            "SORT_ORDER1" => "DESC",
            "SORT_BY2" => "SORT",
            "SORT_ORDER2" => "ASC",
            "FILTER_NAME" => "needed_courses_filter",
            "FIELD_CODE" => array("ID"),
            "PROPERTY_CODE" => array("DESCRIPTION"),
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "SET_TITLE" => "N",
            "SET_BROWSER_TITLE" => "N",
            "SET_META_KEYWORDS" => "Y",
            "SET_META_DESCRIPTION" => "Y",
            "SET_LAST_MODIFIED" => "Y",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
            "ADD_SECTIONS_CHAIN" => "Y",
            "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "INCLUDE_SUBSECTIONS" => "Y",
            "CACHE_TYPE" => "N",
            "CACHE_TIME" => "3600",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "Y",
            "DISPLAY_TOP_PAGER" => "Y",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_SHOW_ALWAYS" => "Y",
            "PAGER_TEMPLATE" => "",
            "PAGER_DESC_NUMBERING" => "Y",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "Y",
            "PAGER_BASE_LINK_ENABLE" => "Y",
            "SET_STATUS_404" => "Y",
            "SHOW_404" => "Y",
            "MESSAGE_404" => "",
            "PAGER_BASE_LINK" => "",
            "PAGER_PARAMS_NAME" => "arrPager",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_ADDITIONAL" => ""
        )
    );
}?>
<?php $APPLICATION->IncludeComponent("bitrix:news.list", "main_page_news", array(
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "AJAX_MODE" => "Y",
        "IBLOCK_ID" => IBlockHelper::getNewsIBlock(),
        "NEWS_COUNT" => 3,
        "SORT_BY1" => "ID",
        "SORT_ORDER1" => "DESC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => array("ID", "DATE_CREATE"),
        "PROPERTY_CODE" => array("DESCRIPTION"),
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "Y",
        "SET_META_DESCRIPTION" => "Y",
        "SET_LAST_MODIFIED" => "Y",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
        "PARENT_SECTION" => "",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "CACHE_TYPE" => "N",
        "CACHE_TIME" => "3600",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "DISPLAY_TOP_PAGER" => "Y",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PAGER_TEMPLATE" => "",
        "PAGER_DESC_NUMBERING" => "Y",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "Y",
        "PAGER_BASE_LINK_ENABLE" => "Y",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",
        "MESSAGE_404" => "",
        "PAGER_BASE_LINK" => "",
        "PAGER_PARAMS_NAME" => "arrPager",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_ADDITIONAL" => ""
    )
);
$array_c =  array_diff(Courses::getIdsByRoleOfCurrentUser(), $needed_courses_filter["ID"]);
$completions = new CourseCompletion();
$complete_ids = $completions->getCompletedItems()->getCourseIds();
$courses_for_role_filter['ID'] = array_diff($array_c, $complete_ids);
$APPLICATION->IncludeComponent("bitrix:news.list", "courses_for_role", array(
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "AJAX_MODE" => "Y",
        "IBLOCK_ID" => IBlockHelper::getCoursesIBlock(),
        "NEWS_COUNT" => 3,
        "SORT_BY1" => "ACTIVE_FROM",
        "SORT_ORDER1" => "DESC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "courses_for_role_filter",
        "FIELD_CODE" => array("ID"),
        "PROPERTY_CODE" => array("DESCRIPTION"),
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "Y",
        "SET_META_DESCRIPTION" => "Y",
        "SET_LAST_MODIFIED" => "Y",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
        "PARENT_SECTION" => "",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "CACHE_TYPE" => "N",
        "CACHE_TIME" => "3600",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "DISPLAY_TOP_PAGER" => "Y",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PAGER_TEMPLATE" => "",
        "PAGER_DESC_NUMBERING" => "Y",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "Y",
        "PAGER_BASE_LINK_ENABLE" => "Y",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",
        "MESSAGE_404" => "",
        "PAGER_BASE_LINK" => "",
        "PAGER_PARAMS_NAME" => "arrPager",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_ADDITIONAL" => ""
    )
);
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "main_page_materials",
    array(
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "AJAX_MODE" => "N",
        "IBLOCK_ID" => IBlockHelper::getMaterialsIBlock(),
        "NEWS_COUNT" => 6,
        "SORT_BY1" => "ACTIVE_FROM",
        "SORT_ORDER1" => "DESC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => array("ID"),
        "PROPERTY_CODE" => array("DESCRIPTION"),
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "Y",
        "SET_META_DESCRIPTION" => "Y",
        "SET_LAST_MODIFIED" => "Y",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
        "PARENT_SECTION" => "",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "CACHE_TYPE" => "N",
        "CACHE_TIME" => "3600",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
        "DISPLAY_TOP_PAGER" => "Y",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PAGER_TEMPLATE" => "",
        "PAGER_DESC_NUMBERING" => "Y",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "Y",
        "PAGER_BASE_LINK_ENABLE" => "Y",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",
        "MESSAGE_404" => "",
        "PAGER_BASE_LINK" => "",
        "PAGER_PARAMS_NAME" => "arrPager",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_ADDITIONAL" => ""
    )
);
?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
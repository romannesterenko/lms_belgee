<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Назначенные курсы");
$id = \Helpers\UrlParamsHelper::getParam('id');
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <div class="content-block">
                <div class="content-nav content-nav--top">
                    <span><a href="/cabinet/dealer/employees/<?=$id?>/">Профиль сотрудника</a></span>
                    <span><a href="/cabinet/dealer/employees/passing/<?=$id?>/">Проходит курсы</a></span>
                    <span><a href="/cabinet/dealer/employees/passed/<?=$id?>/">Пройденные курсы</a></span>
                    <span class="active"><a href="/cabinet/dealer/employees/setted_courses/<?=$id?>/">Назначенные курсы</a></span>
                </div>
                <?php
                $enrollments = new \Teaching\Enrollments();
                $ids = \Teaching\Roles::GetRequiredCourseIdsByUser($id);
                $exist_ids = [];
                foreach ($enrollments->getListByUser($id, false) as $enroll) {
                    $exist_ids[] = $enroll['UF_COURSE_ID'];
                }
                $needed_courses_filter["ID"] = count($exist_ids) > 0 ? array_diff($ids, $exist_ids) : $ids;
                if (count($needed_courses_filter["ID"]) > 0) {
                    $APPLICATION->IncludeComponent("bitrix:news.list", "user_needed_courses", array(
                            "DISPLAY_DATE" => "Y",
                            "DISPLAY_NAME" => "Y",
                            "DISPLAY_PICTURE" => "Y",
                            "DISPLAY_PREVIEW_TEXT" => "Y",
                            "AJAX_MODE" => "Y",
                            "IBLOCK_ID" => \Helpers\IBlockHelper::getCoursesIBlock(),
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
                            "PAGER_TITLE" => "Новости",
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

                /*
                $completed_courses_filter['UF_USER_ID']=$id;
                $completed_courses_filter['UF_IS_COMPLETE']=0;
                $completed_courses_filter['ID']=[];
                $APPLICATION->IncludeComponent("bitrix:highloadblock.list","completed_courses_without_years",Array(
                        "BLOCK_ID" => "2",
                        "CHECK_PERMISSIONS" => "Y",
                        "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
                        "FILTER_NAME" => "completed_courses_filter",
                        "PAGEN_ID" => "page",
                        "ROWS_PER_PAGE" => "15"
                    )
                );
                */?>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?php
global $APPLICATION, $USER;

use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
use Polls\ProcessPoll;
use Teaching\Roles;

$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
?>
<h2 class="h2"><?php $APPLICATION->ShowTitle();?></h2>
<?php $notifications_main_filter['UF_USER_ID']=$USER->GetID();
    $notifications_main_filter['UF_IS_READ']=false;
    $APPLICATION->IncludeComponent("bitrix:highloadblock.list","notifications_cabinet_admin",Array(
            "BLOCK_ID" => "3",
            "CHECK_PERMISSIONS" => "Y",
            "DETAIL_URL" => "",
            "FILTER_NAME" => "notifications_main_filter",
            "PAGEN_ID" => "page",
            "ROWS_PER_PAGE" => "4"
        )
    );
?>


<?php $GMRMessages_main_filter['UF_USER_ID']=$USER->GetID();
$GMRMessages_main_filter['UF_IS_READ']=false;
$APPLICATION->IncludeComponent("bitrix:highloadblock.list","GMRMessages_cabinet_admin",Array(
        "BLOCK_ID" => "5",
        "CHECK_PERMISSIONS" => "Y",
        "DETAIL_URL" => "",
        "FILTER_NAME" => "GMRMessages_main_filter",
        "PAGEN_ID" => "page",
        "ROWS_PER_PAGE" => "6"
    )
);
?>
<?php /*$APPLICATION->IncludeComponent(
    "lms:cabinet.admin.courses.stat",
    "",
    Array(),
    false
);*/

$APPLICATION->IncludeComponent(
    "lms:cabinet.admin.completions.info",
    "teaching_admin",
    Array(),
    false
);

$APPLICATION->IncludeComponent(
    "lms:cabinet.admin.enrolls.confirm",
    "",
    Array(),
    false
);
?>
<?php $completed_courses_filter['UF_USER_ID']=$USER->GetID();
$completed_courses_filter['UF_IS_COMPLETE']=1;
$APPLICATION->IncludeComponent("bitrix:highloadblock.list","completed_courses_cabinet",Array(
        "BLOCK_ID" => "2",
        "CHECK_PERMISSIONS" => "Y",
        "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
        "FILTER_NAME" => "completed_courses_filter",
        "PAGEN_ID" => "page",
        "ROWS_PER_PAGE" => "5"
    )
);
$APPLICATION->IncludeComponent("lms:shedule.courses.list",
    "admin_calendar_list",
    array(
        "MONTH" => $_REQUEST['month']??date('m'),
        "YEAR" => $_REQUEST['year']??date('Y'),
        "FOR_ROLE" => $_REQUEST['role']?? Roles::getByCurrentUser(),
        "PAGE_COUNT" => 3,
    ),
    false
);
global $pollsFilter;
$pollsProcess = new ProcessPoll();
$complete_polls = $pollsProcess->getCompletePollIdsByCurrentUser();
$pollsFilter['UF_ROLES'] = Roles::getByUser();
if(count($complete_polls)>0)
    $pollsFilter['!ID'] = $complete_polls;
$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "polls_cabinet", Array(
    "VIEW_MODE" => "TEXT",	// Вид списка подразделов
    "SHOW_PARENT_NAME" => "Y",	// Показывать название раздела
    "IBLOCK_ID" => IBlockHelper::getPollsIBlock(),	// Инфоблок
    "SECTION_ID" => false,	// ID раздела
    "SECTION_CODE" => "",	// Код раздела
    "SECTION_URL" => "",	// URL, ведущий на страницу с содержимым раздела
    "COUNT_ELEMENTS" => "Y",	// Показывать количество элементов в разделе
    "TOP_DEPTH" => "2",	// Максимальная отображаемая глубина разделов
    "SECTION_FIELDS" => "",	// Поля разделов
    "SECTION_USER_FIELDS" => "",	// Свойства разделов
    "ADD_SECTIONS_CHAIN" => "Y",	// Включать раздел в цепочку навигации
    "CACHE_TYPE" => "N",	// Тип кеширования
    "CACHE_TIME" => "36000000",	// Время кеширования (сек.)
    "CACHE_NOTES" => "",
    "CACHE_GROUPS" => "Y",	// Учитывать права доступа
    "FILTER_NAME" => "pollsFilter",	// Имя массива со значениями фильтра разделов
),
    false
);
$APPLICATION->IncludeComponent("bitrix:news.list","cabinet_page_news",Array(
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "AJAX_MODE" => "Y",
        "IBLOCK_ID" => IBlockHelper::getNewsIBlock(),
        "NEWS_COUNT" => 2,
        "SORT_BY1" => "ACTIVE_FROM",
        "SORT_ORDER1" => "DESC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => Array("ID"),
        "PROPERTY_CODE" => Array("DESCRIPTION"),
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
);?>

<?php

use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
use Teaching\Roles;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?php $APPLICATION->ShowTitle();?></h2>
            <?php
            global $arrFilter;
            $requires = Roles::GetRequiredCourseIdsByUser();
            $arrFilter = [
                'ID' => $requires
            ];
            if(count($arrFilter['ID'])>0) {
                $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "list",
                    array(
                        "IBLOCK_ID" => IBlockHelper::getCoursesIBlock(),
                        "NEWS_COUNT" => 12,
                        "SORT_BY1" => 'ACTIVE_FROM',
                        "SORT_ORDER1" => "DESC",
                        "SORT_BY2" => 'SORT',
                        "SORT_ORDER2" => "ASC",
                        "FIELD_CODE" => $arParams["LIST_FIELD_CODE"],
                        "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["detail"],
                        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                        "IBLOCK_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["news"],
                        "DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
                        "SET_TITLE" => 'N',
                        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                        "MESSAGE_404" => $arParams["MESSAGE_404"],
                        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                        "SHOW_404" => $arParams["SHOW_404"],
                        "FILE_404" => $arParams["FILE_404"],
                        "INCLUDE_IBLOCK_INTO_CHAIN" => $arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
                        "CACHE_TYPE" => "N",
                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                        "PAGER_TITLE" => 'N',
                        "PAGER_TEMPLATE" => 'load_ajax',
                        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                        "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
                        "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
                        "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                        "DISPLAY_DATE" => $arParams["DISPLAY_DATE"],
                        "DISPLAY_NAME" => "Y",
                        "DISPLAY_PICTURE" => $arParams["DISPLAY_PICTURE"],
                        "DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
                        "PREVIEW_TRUNCATE_LEN" => $arParams["PREVIEW_TRUNCATE_LEN"],
                        "ACTIVE_DATE_FORMAT" => $arParams["LIST_ACTIVE_DATE_FORMAT"],
                        "USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
                        "GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
                        "FILTER_NAME" => 'arrFilter',
                        "HIDE_LINK_WHEN_NO_DETAIL" => $arParams["HIDE_LINK_WHEN_NO_DETAIL"],
                        "CHECK_DATES" => $arParams["CHECK_DATES"],
                    ),
                    false
                );
            }?>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
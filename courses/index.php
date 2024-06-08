<?php

use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
use Helpers\UserHelper;
use Teaching\Courses;
use Teaching\Roles;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER, $arrFilter;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
$requires = Roles::GetRequiredCourseIdsByUser();
$requires_role = Roles::GetRequiredCourseIdsByRole(UserHelper::getRoleByCurrentUser());
$for_role = Courses::getIdsByRoleOfCurrentUser();
if(!\Models\User::isTeachingAdmin()){
	$arrFilter = [
		'ID' => array_unique(array_merge($requires, $requires_role, $for_role))
	];
}
if(!$USER->isAdmin() && check_full_array(Courses::getTestList())){
	$arrFilter['!ID'] = array_keys(Courses::getTestList());
}
if($_REQUEST['cat']=='op'||$_REQUEST['cat']=='ppo'){
	if($_REQUEST['cat']=='op'){
		$arrFilter['SECTION_ID'] = 4;
		$arrFilter['INCLUDE_SUBSECTIONS'] = "Y";
	} else {
		$arrFilter['SECTION_ID'] = 17;
		$arrFilter['INCLUDE_SUBSECTIONS'] = "Y";

	}
}
?>

<?php
$APPLICATION->IncludeComponent(
	"bitrix:news", 
	"courses", 
	array(
		"FILTER_NAME" => "arrFilter",
		"ADD_ELEMENT_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BROWSER_TITLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_FIELD_CODE" => array(
			0 => "PREVIEW_PICTURE",
			1 => "",
		),
		"DETAIL_PAGER_SHOW_ALL" => "Y",
		"DETAIL_PAGER_TEMPLATE" => "",
		"DETAIL_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => IBlockHelper::getCoursesIBlock(),
		//"IBLOCK_TYPE" => "timetable",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"LIST_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"LIST_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"MESSAGE_404" => "",
		"META_DESCRIPTION" => "-",
		"META_KEYWORDS" => "-",
		"NEWS_COUNT" => "12",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "load_ajax",
		"PREVIEW_TRUNCATE_LEN" => "",
		"SEF_FOLDER" => "/courses/",
		"SEF_MODE" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "N",
		"SHOW_404" => "N",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "DESC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"USE_CATEGORIES" => "N",
		"USE_FILTER" => "Y",
		"USE_PERMISSIONS" => "N",
		"USE_RATING" => "N",
		"USE_RSS" => "N",
		"USE_SEARCH" => "N",
		"USE_SHARE" => "N",
		"COMPONENT_TEMPLATE" => "courses",
		"SEF_URL_TEMPLATES" => array(
			"news" => "/courses/",
			"section" => "",
			"detail" => "#ELEMENT_CODE#/",
		)
	),
	false
);?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
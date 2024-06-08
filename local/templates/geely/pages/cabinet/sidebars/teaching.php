<?php
global $APPLICATION;
$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "cabinet_menu",
    array(
        "ROOT_MENU_TYPE" => "cabinet",
        "MAX_LEVEL" => "1",
        "CHILD_MENU_TYPE" => "cabinet",
        "USE_EXT" => "N",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "Y",
        "MENU_CACHE_TYPE" => "N",
        "MENU_CACHE_TIME" => "3600",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(
        ),
        "COMPONENT_TEMPLATE" => "cabinet_menu"
    ),
    false
);
$APPLICATION->IncludeComponent(
    "lms:cabinet.admin.menu",
    "teaching",
    Array(),
    false
);
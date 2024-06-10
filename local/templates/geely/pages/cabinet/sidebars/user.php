<?php
global $USER, $APPLICATION;

use Helpers\UserHelper;
use Models\Employee;

if($USER->IsAdmin() || \Models\User::isLMSAdmin()) {
    $APPLICATION->IncludeComponent(
        "lms:cabinet.admin.menu",
        "admin",
        Array(),
        false
    );
}
if(Employee::isInReportsGroup()){
    $APPLICATION->IncludeComponent(
        "lms:cabinet.admin.menu",
        "reports_group",
        Array(),
        false
    );
}
if(Employee::isTrainer()){
    $APPLICATION->IncludeComponent(
        "lms:cabinet.admin.menu",
        "trainer",
        Array(),
        false
    );
}
if(UserHelper::isLocalAdmin()){
    $APPLICATION->IncludeComponent(
        "lms:cabinet.admin.menu",
        "diller",
        Array(),
        false
    );
}
if(UserHelper::isTeachingAdmin()){
    $APPLICATION->IncludeComponent(
        "lms:cabinet.admin.menu",
        "teaching",
        Array(),
        false
    );
}
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
);?>
<?php $APPLICATION->IncludeComponent(
    "lms:cabinet.courses.section",
    "",
    array(),
    false
);?>
<?php /*$APPLICATION->IncludeComponent(
    "lms:upcoming.courses",
    "",
    array(),
    false
);*/?>
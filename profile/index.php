
<?php
const NEED_AUTH = true;
use Bitrix\Main\Localization\Loc;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php $APPLICATION->IncludeComponent(
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
                <?php $APPLICATION->IncludeComponent(
                    "lms:upcoming.courses",
                    "",
                    array(),
                    false
                );?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <?php $APPLICATION->IncludeComponent(
                "bitrix:main.profile",
                "profile",
                Array(),
                false
            );?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
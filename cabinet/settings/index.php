<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <?php $APPLICATION->IncludeComponent(
                "lms:cabinet.settings.showmaterials",
                "",
                array(),
                false
            );?>
            <?php $APPLICATION->IncludeComponent(
                "lms:cabinet.settings.changepassword",
                "",
                array(),
                false
            );?>
            <?php $APPLICATION->IncludeComponent(
                "lms:cabinet.settings.showmenotifications",
                "",
                array(),
                false
            );?>
            <?php $APPLICATION->IncludeComponent(
                "lms:cabinet.settings.additionalcontacts",
                "",
                array(),
                false
            );?>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
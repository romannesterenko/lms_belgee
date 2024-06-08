<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$APPLICATION->SetTitle(GetMessage('MAIN_TITLE'));
$enroll = new \Teaching\Enrollments();
?>

<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?php $APPLICATION->ShowTitle();?></h2>
        <?php $APPLICATION->IncludeComponent(
            "lms:cabinet.courses.enrolls",
            "",
            Array(),
            false
        );?>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?php
const NEED_AUTH=true;
use Helpers\PageHelper;
use Teaching\Enrollments;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$APPLICATION->SetTitle(GetMessage('MAIN_TITLE'));
$enroll = new Enrollments();?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <?php require_once(PageHelper::getPageForCabinet());?>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
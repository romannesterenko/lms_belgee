<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2">Инструменты администратора</h2>
        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/sync/dealers.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Миграция дилеров</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>
        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/sync/users.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Миграция пользователей</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>


    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
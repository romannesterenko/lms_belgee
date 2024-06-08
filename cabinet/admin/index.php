<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

LocalRedirect('/cabinet/admin/reports/');?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?= Loc::getMessage('ADMIN_DASHBOARD_TITLE') ?></h2>
        <div class="content-block  content-block--margin">
            <h3 class="h3 center"><?= Loc::getMessage('ADMIN_DASHBOARD_REPORTS_LIST') ?></h3>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report8.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Отчет по посещаемости тренингов</span>
                        <span class="timetable-item__text">Отчет по посещаемости тренингов</span>
                  </span>
                </a>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report1.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title"><?= Loc::getMessage('ITEM_TITLE') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('ITEM_TEXT') ?></span>
                  </span>
                </a>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report2.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title"><?= Loc::getMessage('ITEM_TITLE_2') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('ITEM_TEXT_2') ?></span>
                  </span>
                </a>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report3.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title"><?= Loc::getMessage('ITEM_TITLE_3') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('ITEM_TEXT_3') ?></span>
                  </span>
                </a>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report4.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title"><?= Loc::getMessage('ITEM_TITLE_4') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('ITEM_TEXT_4') ?></span>
                  </span>
                </a>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention">
                <a href="/cabinet/admin/reports/report5.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title"><?= Loc::getMessage('ITEM_TITLE_5') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('ITEM_TEXT_5') ?></span>
                  </span>
                </a>
            </div>
        </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
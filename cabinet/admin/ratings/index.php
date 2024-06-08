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
            <h2 class="h2">Отчеты по рейтингам</h2>
            <div class="content-block  content-block--margin">

                <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                    <a href="/cabinet/admin/reports/15/">
                        <span class="timetable-item__content">
                            <span class="timetable-item__title">№15. Рейтинг по % аттестованного персонала</span>
                            <span class="timetable-item__text">Рейтинг дилеров по % аттестованного персонала</span>
                        </span>
                    </a>
                    <div class="link_loading_spinner">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                    </div>
                </div>
                <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                    <a href="/cabinet/admin/reports/16/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№16. Рейтинг по обученности</span>
                        <span class="timetable-item__text">Рейтинг дилеров по обученности персонала</span>
                  </span>
                    </a>
                    <div class="link_loading_spinner">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                    </div>
                </div>
                <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                    <a href="/cabinet/admin/reports/17/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№17. Рейтинг обученности по ролям</span>
                        <span class="timetable-item__text">Рейтинг обученности по ролям</span>
                  </span>
                    </a>
                    <div class="link_loading_spinner">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                    </div>
                </div>
                <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                    <a href="/cabinet/admin/reports/21/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№21. Рейтинг обученности дилеров по курсам</span>
                        <span class="timetable-item__text">Рейтинг обученности дилеров по курсам</span>
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
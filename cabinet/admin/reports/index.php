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
        <h2 class="h2"><?= Loc::getMessage('ADMIN_DASHBOARD_REPORTS_LIST') ?></h2>
        <div class="content-block  content-block--margin">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/1/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№1. <?= Loc::getMessage('DEALER_REPORT') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('DEALER_REPORT_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <?php
            //Задача №№2,3,4,6 скрыть

            /*<div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/report2.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№2. <?= Loc::getMessage('TIME_REPORT') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('TIME_REPORT_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/report3.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№3. <?= Loc::getMessage('COURSES_IN_DEALER') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('COURSES_IN_DEALER_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/report4.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№4. <?= Loc::getMessage('EXAM_REPORT') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('EXAM_REPORT_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>*/?>

            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/5/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№5. <?= Loc::getMessage('PRODUCT_REPORT') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('PRODUCT_REPORT_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>

            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/5.1/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№5.1. Отчет по продуктивности за год</span>
                        <span class="timetable-item__text">Отчет по продуктивности за год</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <?php
            //Задача №№2,3,4,6 скрыть

            /*
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/report6_filter.php">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№6. Отчет по ролям</span>
                        <span class="timetable-item__text">Отчет по ролям</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>;*/?>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/7/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№7. <?= Loc::getMessage('TEK_KADR_REPORT') ?></span>
                        <span class="timetable-item__text"><?= Loc::getMessage('TEK_KADR_REPORT_DESCR') ?></span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/8/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№8. Отчет по посещаемости тренингов</span>
                        <span class="timetable-item__text">Отчет по посещаемости тренингов</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/8.1/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№8.1 Отчет по посещаемости тренингов (новый шаблон)</span>
                        <span class="timetable-item__text">Отчет по посещаемости тренингов (новый шаблон)</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>

            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/8.2/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№8.2 Отчет по ресертификации</span>
                        <span class="timetable-item__text">Отчет по ресертификации</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/9/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№9. Отчет по записям на курсы</span>
                        <span class="timetable-item__text">Отчет по записям на курсы</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/10/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№10. Расписание модулей в расписании на месяц</span>
                        <span class="timetable-item__text">Расписание модулей в расписании на месяц</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/11/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№11. Отчет по тестированию</span>
                        <span class="timetable-item__text">Отчет по тестированию</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/12/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№12. Штатное расписание</span>
                        <span class="timetable-item__text">Штатное расписание</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>


            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/13/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№13. Отчет по дилерской сети</span>
                        <span class="timetable-item__text">Отчет по дилерской сети</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/14/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№14. Выгрузка пользователей</span>
                        <span class="timetable-item__text">Выгрузка пользователей</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/18/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№18. Форма обратной связи</span>
                        <span class="timetable-item__text">Отчет по форме обратной связи</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/19/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№19. Отчёт по уровням</span>
                        <span class="timetable-item__text">Отчет по уровням обучения</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/20/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№20. Балансы дилеров</span>
                        <span class="timetable-item__text">Отчет по балансам дилеров</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/reports/22/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">№22. Отчет по реализации</span>
                        <span class="timetable-item__text">Отчет по реализации</span>
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
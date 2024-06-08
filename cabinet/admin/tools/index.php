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
                <a href="/cabinet/admin/tools/manual_add/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Ручное добавление прохождений</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/test_questions_add/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Загрузка вопросов к тестам</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/load_regional_managers/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Загрузка региональных менеджеров</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/merge_users/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Объединение сотрудников</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/add_attempts/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Добавление попыток прохождения</span>
                  </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/kick_participaints/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Удаление участников из тренингов</span>
                    </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/deactivate_dealers/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Деактивация дилера</span>
                    </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/assigning_courses/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Назначение курсов</span>
                    </span>
                </a>
                <div class="link_loading_spinner">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 20px;">
            <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                <a href="/cabinet/admin/tools/sync/">
                    <span class="timetable-item__content">
                        <span class="timetable-item__title">Миграции</span>
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
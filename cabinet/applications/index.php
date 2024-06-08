<?php
use Helpers\PageHelper;
use Models\Application;
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle('Список заявок');
$applications = Application::getList([
    "PROPERTY_ROLES" => \Teaching\Roles::getByCurrentUser()
],
['ID', 'NAME', 'PREVIEW_TEXT']);?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <form class="content">
            <h2 class="h2 center lowercase">Список заявок</h2>
            <div class="content-block  content-block--margin">
                <?php if (check_full_array($applications)) {
                    foreach ($applications as $application) { ?>
                        <div class="timetable-item timetable-item--small timetable-item--attention go_to_report">
                            <a href="/cabinet/applications/<?= $application['ID'] ?>/">
                                <span class="timetable-item__content">
                                    <span class="timetable-item__title"><?= $application['NAME'] ?></span>
                                    <span class="timetable-item__text"><?= $application['PREVIEW_TEXT'] ?></span>
                                </span>
                            </a>
                            <div class="link_loading_spinner">
                                <img src="<?= SITE_TEMPLATE_PATH ?>/images/spinner.gif" alt="">
                            </div>
                        </div>
                    <?php }
                } else {?>
                    <p>Для вашей роли заявок не предусмотрено</p>
                <?php }?>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
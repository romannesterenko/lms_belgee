<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
?>

    <h2 style="margin-top: 40px">Сервисные ссылки</h2>
    <div class="content-block" style="margin-bottom: 50px; display: flex; flex-direction: column">
        <a href="/service_scripts/logs.php">Логи</a>
        <a href="/service_scripts/check_tg_links.php">Обновление ссылок Telegram</a>
        <a href="/service_scripts/check_tg_users.php">Обновление Пользователей Telegram</a>
        <a href="/service_scripts/check_users_script.php">Сверка пользователей Telegram</a>
        <a href="/service_scripts/get_last_scorm_compl.php">Последнее прохождение Scorm</a>
    </div>

<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
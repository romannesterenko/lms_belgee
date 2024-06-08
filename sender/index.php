<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Localization\Loc;?>
    <a href="/sender/admins/index.php" class="btn btn--md"><?= Loc::getMessage('SEND_TO_ADMINS') ?></a><br/><br/>
    <a href="/sender/employees/index.php" class="btn btn--md"><?= Loc::getMessage('SEND_TO_EMPLOYEES') ?></a><br/><br/>
    <a href="/sender/free_places/index.php" class="btn btn--md"><?= Loc::getMessage('SEND_FREE_PLACES_INFO') ?></a><br/><br/>
    <a href="/sender/recertification/index.php" class="btn btn--md">Уведомления о ресертификации</a><br/><br/>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
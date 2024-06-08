<?php
const NEED_AUTH = true;
use Bitrix\Main\Localization\Loc;
use Helpers\UserHelper;
use Models\User;
use Notifications\EmailNotifications;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$sended = false;
foreach (User::getUserIdsByFilter(['UF_LOCAL_ADMIN'=>1, 'UF_INVITE_MAILING' => 20]) as $user){
    EmailNotifications::sendToAdminDC($user['ID']);
    echo Loc::getMessage('SENDED').$user['LAST_NAME'].' '.$user['NAME'].'<br/>';
    UserHelper::setUserValue('UF_INVITE_MAILING', 21, $user['ID']);
    $sended = true;
}
if(!$sended)
    echo Loc::getMessage('NO_EMPLOYEES_FOR_SEND');

?>
    <br/>
    <br/>
    <br/>

    <a href="/sender/" class="btn btn--md btn--fixed"><?= Loc::getMessage('BACK') ?></a><br/><br/>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION;?>
<h3 class="h3 mobile-hide"><?=GetMessage('ADMIN_MENU_TITLE')?></h3>
<div class="materials-menu move-1 aside-block">
    <ul>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/diller/profile/'?" class='active'":""?>><a href="/cabinet/diller/profile/"><?=GetMessage('ADMIN_MENU_COMMON')?></a></li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/notifications/'?" class='active'":""?>><a href="/cabinet/notifications/"><?=GetMessage('ADMIN_MENU_NOTIFICATIONS')?></a></li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/settings/'?" class='active'":""?>><a href="/cabinet/settings/"><?=GetMessage('ADMIN_MENU_SETTINGS')?></a></li>
    </ul>
</div>



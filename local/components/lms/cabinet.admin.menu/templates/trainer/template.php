<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION;?>
<h3 class="h3"><?=GetMessage('ADMIN_TEACHING_MENU_TITLE')?></h3>

<div class="side-menu aside-block">
    <ul>
        <li<?=CSite::InDir('/cabinet/trainer/')?" class='active'":""?>>
            <a href="/cabinet/trainer/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#work-table"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_TEACHING_MENU_WORKSPACE')?>
            </a>
        </li>
    </ul>
</div>



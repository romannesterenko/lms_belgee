<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION, $USER;?>
<h3 class="h3">Группа отчеты</h3>

<div class="side-menu aside-block">
    <ul>
        <li<?=CSite::InDir('/cabinet/admin/reports/')?" class='active'":""?>>
            <a href="/cabinet/admin/reports/">
                    <span class="icon">
                      <svg width="16px" height="18px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_DILLER_MENU_REPORTS')?>
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/admin/ratings/')?" class='active'":""?>>
            <a href="/cabinet/admin/ratings/">
                    <span class="icon">
                      <svg width="16px" height="18px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Рейтинги
            </a>
        </li>
    </ul>
</div>



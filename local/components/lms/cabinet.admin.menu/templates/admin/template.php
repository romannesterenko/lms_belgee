<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION, $USER;?>
<h3 class="h3"><?=GetMessage('ADMIN_MENU_TITLE')?></h3>

<div class="side-menu aside-block">
    <ul>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/admin/'?" class='active'":""?>>
            <a href="/cabinet/admin/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#work-table"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_DILLER_MENU_WORKSPACE')?>
            </a>
        </li>
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
        <li<?=CSite::InDir('/cabinet/admin/applications/')?" class='active'":""?>>
            <a href="/cabinet/admin/applications/">
                <span class="icon">
                  <svg width="16px" height="18px">
                    <use xlink:href="#report"></use>
                  </svg>
                </span>
                Заявки
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/admin/polls/')?" class='active'":""?>>
            <a href="/cabinet/admin/polls/">
                    <span class="icon">
                      <svg width="16px" height="18px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_DILLER_MENU_POLLS')?>
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/admin/tools/')?" class='active'":""?>>
            <a href="/cabinet/admin/tools/">
                    <span class="icon">
                      <svg width="16px" height="18px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Инструменты
            </a>
        </li>

    </ul>
</div>



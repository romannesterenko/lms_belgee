<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);?>
<div class="materials-menu move-1 aside-block">
    <ul>
        <?php foreach ($arResult['SECTIONS'] as $section){?>
            <li<?=$APPLICATION->GetCurPage()==$section['SECTION_PAGE_URL']?' class="active"':''?>><a href="<?=$section['SECTION_PAGE_URL']?>"><?=$section['NAME']?><span><?=$section['ELEMENT_CNT']>0?$section['ELEMENT_CNT']:''?></span></a></li>
        <?php }?>
    </ul>
</div>
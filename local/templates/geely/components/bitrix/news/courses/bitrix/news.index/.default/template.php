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
$this->setFrameMode(true);
?>
<div class="news-index">
    <?php foreach($arResult["IBLOCKS"] as $arIBlock):?>
        <?php if(count($arIBlock["ITEMS"])>0):?>
		<b><?=$arIBlock["NAME"]?></b>
		<ul>
            <?php foreach($arIBlock["ITEMS"] as $arItem):?>
			<li><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a></li>
            <?php endforeach;?>
		</ul>
        <?php endif?>
    <?php endforeach;?>
</div>

<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER, $APPLICATION;?>
<tr>
    <td><a href="/courses/<?=$arResult['ITEM']['CODE']?>/"><?=$arResult['ITEM']['NAME']?></a></td>
    <td>
        <span class="<?=strtolower($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT'])?>"> <?=$arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']?></span>
    </td>
</tr>
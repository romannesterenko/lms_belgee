<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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
    <td>
        <?php if($arResult['ITEM']['HAS_FREE_PLACES']){?>
            <span class="table-place"><span><a href="/courses/<?=$arResult['ITEM']['CODE']?>/"><?=$arResult['ITEM']['FREE_PLACES']?></a> </span> / <?=$arResult['ITEM']['LIMIT']?></span>
        <?php }?>
    </td>
    <td><?=$arResult['ITEM']['LIMIT']?$arResult['ITEM']['LIMIT'].Loc::getMessage('MEN'):""?> </td>
    <td><?php if($arResult['ITEM']['HAS_DATES']){?><?=$arResult['ITEM']['BEGIN_DATE']?><?php }?></td>
    <td><?php if($arResult['ITEM']['HAS_TEST']){?><span class="table-check"><img src="<?=SITE_TEMPLATE_PATH?>/images/table-check.svg" alt=""></span><?php }?></td>
    <td>
        <?php if($arResult['ITEM']['REGISTER_BUTTON']['NEED_SHOW']){?>
            <a href="javascript:void(0)" class="underline detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=GetMessage('ENROLL')?></a>
        <?php }?>
    </td>
</tr>
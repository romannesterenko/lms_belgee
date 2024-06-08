<?php use Helpers\DateHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER, $APPLICATION;
?>
<div class="timetable-item list_item_card" data-id="<?=$arResult['ITEM']['ID']?>">
    <?php if($_REQUEST['ajax_card']=='Y')
        $APPLICATION->RestartBuffer();?>
        <a href="<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['DETAIL_PAGE_URL']?>">
            <span class="timetable-item__image">
                <img src="<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_PICTURE']?>" alt="">
                <span class="timetable-item__category" data-id="<?=$arResult['ITEM']['ID']?>"> <span class="icon icon-check"></span><?=$arResult['ITEM']['REGISTRATION_COURSE']['TEXT']?></span>
            </span>
            <span class="timetable-item__content">
                <span class="timetable-item__top">
                    <?php if(!$arResult['ITEM']['IS_FREE']){?>
                        <span class="timetable-item__date">
                            <span class="icon icon-calendar"></span><?=$arResult['ITEM']['PROPERTY_BEGIN_DATE_VALUE']?> - <?=$arResult['ITEM']['PROPERTY_END_DATE_VALUE']?>
                        </span>
                    <?php }?>
                    <span class="timetable-item__status<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_FORMAT_VALUE']=='Online'?'':' offline'?>"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_FORMAT_VALUE']?></span>
                </span>
                <span class="timetable-item__title"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['NAME']?></span>
                <span class="timetable-item__text"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_TEXT']?></span>
                <span class="timetable-item__bottom">
                    <span class="timetable-item__cost">
                        <span class="icon icon-purse"></span>
                        <?php if(!empty($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'])&&(int)$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE']>0){?>
                            <?=number_format($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'], '0', '', ' ')?> <?=GetMessage('CURRENCY')?>
                        <?php }else{?>
                            <?=GetMessage('FREE_ACCESS')?>
                        <?php }?>
                    </span>
                    <?php if(!$arResult['ITEM']['IS_FREE']){?>
                        <span class="timetable-item__numbers"><?=GetMessage('FREE_PLACES')?> - <?=$arResult['ITEM']['FREE_PLACES']?> / <span><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_LIMIT_VALUE']??30?></span></span>
                    <?php }?>
                </span>
                <div class="timetable-item__discription"><?=GetMessage('FOR')?><?=$arResult['ITEM']['FOR_ROLES']?></div>
            </span>
        </a>
    <?php if($arResult['ITEM']['REGISTRATION_COURSE']['SHOW_REGISTER_BUTTON']){
        if($arResult['ITEM']['IS_FREE']){?>
            <a href="javascript:void(0)" onclick="showCalendar('<?=$arResult['ITEM']['ID']?>')" class="btn"><?=GetMessage('ENROLL')?></a>
        <?php }else{?>
            <a href="javascript:void(0)" onclick="showEnrollForm('<?=$arResult['ITEM']['ID']?>', '<?=$arResult['ITEM']['PROPERTY_BEGIN_DATE_VALUE']?>', '<?= DateHelper::getHumanDate($arResult['ITEM']['~PROPERTY_BEGIN_DATE_VALUE'], 'H:i')?>')" class="btn"><?=GetMessage('ENROLL')?></a>
        <?php }?>
    <?php }?>
    <?php if($_REQUEST['ajax_card']=='Y')
        die();?>
</div>
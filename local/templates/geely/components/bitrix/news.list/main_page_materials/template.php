<?php use Helpers\DateHelper;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
<div class="content-section">
    <div class="content-section__head">
        <h2 class="h2"><?=GetMessage('MAIN_PAGE_MATERIALS_TITLE')?></h2>
        <div class="content-section__head-link">
            <a href="/knoledge_base/">
                <?=GetMessage('MAIN_PAGE_MATERIALS_ALL_MATERIALS_LINK')?>
                <span class="icon icon-arrow-link"></span>
            </a>
        </div>
    </div>
    <div class="content-block">

        <div class="carousel owl-carousel">
            <?php foreach($arResult["ITEMS"] as $arItem):?>
                <?php
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));?>
                <div class="item">
                    <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="post-item<?=$arItem['PROPERTIES']['FORMAT']['VALUE_ENUM_ID']==1?' post-item--video':''?>">
                            <span class="post-item__image ">
                              <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="">
                                <?php if($arItem['PROPERTIES']['FORMAT']['VALUE_ENUM_ID']):?>
                                <span class="post-item__material">
                                    <?php if($arItem['PROPERTIES']['FORMAT']['VALUE_ENUM_ID']==1){?>
                                        <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/video-icon.svg" alt=""></span>
                                    <?php }elseif ($arItem['PROPERTIES']['FORMAT']['VALUE_ENUM_ID']==2){?>
                                        <span class="icon icon-pdf"></span>
                                    <?php }?>
                                </span>
                                <?php endif;?>
                            </span>
                        <span class="post-item__content">
                            <span class="post-item__top">
                                <span class="post-item__date">
                                    <span class="icon"><span class="icon-calendar"></span> </span>
                                    <?= DateHelper::getHumanDate($arItem['DATE_CREATE']);?>
                                </span>
                            </span>
                            <span class="post-item__title"><?=$arItem['NAME']?></span>
                        </span>
                    </a>
                </div>
            <?php endforeach;?>
        </div>
        <div class="btn-center margin">
            <a href="/knoledge_base/" class="btn "><?=GetMessage('MAIN_PAGE_MATERIALS_VIEW_ALL')?></a>
        </div>
    </div>
</div>
<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;

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
<div class="content">
    <div class="content-block">
        <div class="materials-block">
            <?php /*
            <div class="materials-block__head">
                <div class="materials-block__col">
                    <div class="select-block inline">
                        <label for="">Тема:</label>
                        <div class="select">
                            <select class="select2">
                                <option>Финансы компании</option>
                                <option>очень длинный длинный текст</option>
                                <option>Option 3</option>
                                <option>Option 4</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="materials-block__col">
                    <div class="select-block inline">
                        <label for="">Формат:</label>
                        <div class="select">
                            <select class="select2">
                                <option>видео-ролики</option>
                                <option>Option 2</option>
                                <option>Option 3</option>
                                <option>Option 4</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
*/?>
            <div class="materials-block__content">
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
                <?php if(!$arResult["ITEMS"]):?>
                   <div style="margin: 40px 0; text-align: center; width: 100%"><?= Loc::getMessage('NO_MATERIALS') ?></div>
                <?php endif;?>
            </div>

<!--            <div class="btn-center">-->
<!--                <a href="#" class="btn ">Смотреть еще</a>-->
<!--            </div>-->
        </div>
        <?=$arResult["NAV_STRING"]?>
    </div>
</div>

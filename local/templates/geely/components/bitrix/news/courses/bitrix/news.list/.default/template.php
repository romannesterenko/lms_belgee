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
<div class="content">
    <div class="content-block">
        <div class="materials-block courses_list">
            <div class="materials-block__content">
                <?php if($_REQUEST['ajax_load_more']=='Y')
                    $APPLICATION->RestartBuffer();?>
                <?php foreach($arResult["ITEMS"] as $arItem):?>
                    <?php $APPLICATION->IncludeComponent("lms:course.card",
                        "card",
                        array(
                            "COURSE_ID" => $arItem['ID'],
                        ),
                        false
                    );?>
                <?php endforeach;?>
                <?php if($_REQUEST['ajax_load_more']=='Y')
                    die();?>
            </div>
            <?=$arResult["NAV_STRING"]?>
        </div>
    </div>
</div>

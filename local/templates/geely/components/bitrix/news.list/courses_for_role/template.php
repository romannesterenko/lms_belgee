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
<div class="content-section">
    <div class="content-section__head">
<!--        <h2 class="h2"><?php //=GetMessage('MAIN_COURSES_FOR_ROLE_FOR')?> --><?php //=$arResult['TITLE']?><!--</h2>-->
        <h2 class="h2"><?=GetMessage('MAIN_COURSES_FOR_ROLE_TITLE')?></h2>
        <div class="content-section__head-link">
            <a href="/courses/">
                <?=GetMessage('MAIN_COURSES_FOR_ROLE_ALL_COURSES_LINK')?>
                <span class="icon icon-arrow-link"></span>
            </a>
        </div>

    </div>

    <div class="content-block">

        <div class="carousel owl-carousel">
            <?php foreach ($arResult['ITEMS'] as $arItem){?>
                <?php $APPLICATION->IncludeComponent("lms:course.card",
                    "card",
                    array(
                        "COURSE_ID" => $arItem['ID'],
                    ),
                    false
                );?>
            <?php }?>
        </div>
        <div class="btn-center margin">
            <a href="/courses/" class="btn "><?=GetMessage('MAIN_COURSES_FOR_ROLE_VIEW_ALL')?></a>
        </div>
    </div>
</div>

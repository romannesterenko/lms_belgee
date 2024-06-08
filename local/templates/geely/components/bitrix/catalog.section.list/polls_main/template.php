<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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
<?php if(check_full_array($arResult['SECTIONS'])){?>
<h2 class="h2 mt-20"><?=GetMessage('POLLS_CABINET_TITLE')?></h2>
<div class="content-block content-block--margin">

    <div class="course-list">
        <?php foreach ($arResult['SECTIONS'] as $section){?>
        <div class="course-list-item">
                <span class="course-list-item__icon">
                  <svg class="icon">
                    <use xlink:href="#document"></use>
                  </svg>
                </span>
            <span class="course-list-item__title">
                <a href="<?=$section['SECTION_PAGE_URL']?>">«<?=$section['NAME']?>»</a>
            </span>
            <span class="course-list-item__btn">
                <a href="<?=$section['SECTION_PAGE_URL']?>">
                    <span class="icon icon-arrow-link"></span>
                </a>
            </span>
        </div>
        <?php }?>
    </div>


</div>
<?php }?>
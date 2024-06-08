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
<div class="content-section__head">
    <h3 class="h3"><?=GetMessage('MAIN_NEWS_TITLE')?></h3>
    <div class="content-section__head-link">
        <a href="/news/">
            <?=GetMessage('MAIN_NEWS_ARCHICE_LINK')?>
            <span class="icon icon-arrow-link"></span>
        </a>
    </div>
</div>

<div class="content-block">
    <div class="row">
        <?php foreach ($arResult['ITEMS'] as $key => $arItem){
            if($key==2) break;?>
        <div class="col-6">
            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="post-item<?=$arItem['HAS_VIDEO']?' post-item--video':''?>">
                    <span class="post-item__image ">
                      <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="">
                    </span>
                <span class="post-item__content">
                  <span class="post-item__top">
                    <span class="post-item__date">
                      <span class="icon"><span class="icon-calendar"></span> </span>
                      <?= DateHelper::getHumanDate($arItem['DATE_CREATE'])?>
                    </span>
                  </span>
                  <span class="post-item__title"><?=$arItem['NAME']?></span>
                  <span class="post-item__text"><?=$arItem['PREVIEW_TEXT']?></span>
                </span>
            </a>
        </div>
        <?php }?>

    </div>

    <div class="btn-center">
        <a href="/news/" class="btn "><?=GetMessage('MAIN_NEWS_SHOW_MORE')?></a>
    </div>

</div>
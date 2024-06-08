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
?>
<div class="content-block text-block content-block--margin">


    <div class="text-align-center-image">
<!--        <span class="text-align-center-image__image"><img src="--><?php //=$arResult['DETAIL_PICTURE']['SRC']?><!--" alt=""></span>-->
        <div><?=$arResult['DETAIL_TEXT']?></div>
        <p><?=$arResult['TEXT_BLOCK_1']['~VALUE']['TEXT']?></p>

    </div>

    <?php if(!empty($arResult['YOUTUBE_LINK']['VALUE'])){?>
        <div class="video-content">
            <div class="video" data-video-id="<?=$arResult['YOUTUBE_LINK']['VALUE']?>">
                <div class="video-layer">
                    <div class="video-placeholder">
                        <!-- ^ div is replaced by the YouTube video -->
                    </div>
                </div>
                <div class="video-preview" style="background: url(<?=SITE_TEMPLATE_PATH?>/images/big-img-2.jpg) 50% 50% no-repeat">
                    <!-- this icon would normally be implemented as a character in an icon font or svg spritesheet, or similar -->
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/play-youtube.svg" class="video-play" alt="">
                </div>
            </div>
        </div>
    <?php }?>
    <?php if(!empty($arResult['TEXT_BLOCK_2']['~VALUE']['TEXT'])){?>
        <?=$arResult['TEXT_BLOCK_2']['~VALUE']['TEXT']?>
    <?php }?>
    <?php /*if($arResult['COURSE_IN_TEXT']['VALUE']>0){*/?><!--
    <div class="text-content">
    <div class="course-attention">
        <a href="">
              <span class="course-attention__image">
                <img src="/local/templates/geely/images/img-4.jpg" alt="">
                <span class="course-attention__category"> <span class="icon icon-check"></span><?php /*= Loc::getMessage('OB_COURSE') */?></span>
              </span>
            <span class="course-attention__content">
                <span class="course-attention__attention">
                  <span class="icon">!</span>
                  Внимание! Для специалистов отдела продаж!
                </span>
                <span class="course-attention__title">Курс Артема Соловьева «Как удержать стабильность компании в
                  периоды различных кризисов в стране»

                </span>
                <span class="course-attention__text">В процессе прохождения курса вы повысите свой уровень знаний для
                  максимально быстрой продажи лидирующей позиции, и еще сделаете дополнительные продажи.</span>
                <span class="course-attention__bottom">
                  <span class="course-attention__cost">
                    <span class="icon icon-purse"></span>
                    12 000 руб.
                  </span>
                  <span class="course-attention__numbers">Свободно - 10 / <span>30</span></span>
                </span>
              </span>
        </a>
    </div>
    </div>
    --><?php /*}*/?>
    <?php if(!empty($arResult['TEXT_BLOCK_3']['~VALUE']['TEXT'])){?>
        <?=$arResult['TEXT_BLOCK_3']['~VALUE']['TEXT']?>
    <?php }?>
    <?php if(is_array($arResult['SLIDER']['VALUE'])) if(count($arResult['SLIDER']['VALUE'])>0){?>
    <div class="text-content">
        <div class="content-slider owl-carousel">
            <?php foreach($arResult['SLIDER']['VALUE'] as $image_id){?>
                <div class="item"><img src="<?=CFile::GetPath($image_id)?>" alt=""></div>
            <?php }?>
        </div>
    </div>
    <?php }?>

    <?php if(!empty($arResult['TEXT_BLOCK_4']['~VALUE']['TEXT'])){?>
        <?=$arResult['TEXT_BLOCK_4']['~VALUE']['TEXT']?>
    <?php }?>
</div>



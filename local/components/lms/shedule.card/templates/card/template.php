<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER, $APPLICATION;
?>
<div class="item course_item" data-page="<?=$_REQUEST['PAGEN_1']??1?>">
    <a href="<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['DETAIL_PAGE_URL']?>" class="post-item">
        <span class="post-item__image">
          <img src="<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_PICTURE']?>" alt="">
          <span class="post-item__category"> <span class="icon icon-check"></span><?=$arResult['ITEM']['REGISTRATION_COURSE']['TEXT']?></span>
        </span>
        <span class="post-item__content">
          <span class="post-item__top">
              <?php if(!$arResult['ITEM']['IS_FREE']){?>
                    <span class="post-item__date">
                        <span class="icon"><span class="icon-calendar"></span> </span>
                        <?=$arResult['ITEM']['PROPERTY_BEGIN_DATE_VALUE']?> - <?=$arResult['ITEM']['PROPERTY_END_DATE_VALUE']?>
                    </span>
              <?php }?>
              <span class="post-item__status"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_FORMAT_VALUE']?></span>
          </span>
          <span class="post-item__title"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['NAME']?></span>
            <?php if($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_TEXT']){?>
                <span class="post-item__text"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_TEXT']?></span>
            <?php }?>
          <span class="post-item__bottom">
            <span class="post-item__cost">
              <span class="icon icon-purse"></span>
              <?php if(!empty($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'])&&(int)$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE']>0){?>
                  <?=number_format($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'], '0', '', ' ')?> <?=GetMessage('CURRENCY')?>
              <?php }else{?>
                  <?=GetMessage('FREE_ACCESS')?>
              <?php }?>
            </span>
              <?php if(!$arResult['ITEM']['IS_FREE']){?>
                  <span class="post-item__numbers"><?=GetMessage('FREE_PLACES')?> - <?=$arResult['ITEM']['FREE_PLACES']?> / <span><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_LIMIT_VALUE']??30?></span></span>
              <?php }?>
          </span>
        </span>
    </a>
</div>
<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $USER, $APPLICATION; ?>
<div class="item course_item" data-page="<?= $_REQUEST['PAGEN_1'] ?? 1 ?>">
    <a href="<?= $arResult['ITEM']['DETAIL_PAGE_URL'] ?>" class="post-item">
        <span class="post-item__image">
            <img src="<?= $arResult['ITEM']['PREVIEW_PICTURE'] > 0 ? CFile::GetPath($arResult['ITEM']['PREVIEW_PICTURE']) : SITE_TEMPLATE_PATH . '/images/img-3.jpg' ?>"
                 alt="<?= $arResult['ITEM']['NAME'] ?>">
            <?php if ($arResult['ITEM']['NEED_LABEL']) { ?>
                <span class="post-item__category"><span class="icon icon-check"></span><?= $arResult['ITEM']['LABEL'] ?></span>
            <?php } ?>
        </span>
        <span class="post-item__content">
          <span class="post-item__top">
              <?php if ($arResult['ITEM']['HAS_DATES']) { ?>
                  <span class="post-item__date">
                        <span class="icon"><span class="icon-calendar"></span></span>
                        <?php if ($arResult['ITEM']['BEGIN_DATE_WITHOUT_FORMATTING']==$arResult['ITEM']['END_DATE_WITHOUT_FORMATTING']){?>
                            <?= $arResult['ITEM']['BEGIN_DATE'] ?>
                        <?php } else {?>
                            <?= $arResult['ITEM']['BEGIN_DATE'] ?> - <?= $arResult['ITEM']['END_DATE'] ?>
                        <?php }?>
                    </span>
              <?php } ?>
              <span class="post-item__status <?= strtolower($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']) ?>"><?= $arResult['ITEM']['PROPERTIES']['COURSE_FORMAT'] ?></span>
          </span>
            <?php if($arResult['ITEM']['IS_FOR_SINGLE_STUDY']){?>
                <span class="post-item__top">
                    <span class="post-item__cost"><?=GetMessage('SINGLE_STUDY_VALUE')?></span>
                </span>
            <?php }?>
          <span class="post-item__title"><?= $arResult['ITEM']['NAME'] ?></span>
            <?php if ($arResult['ITEM']['PREVIEW_TEXT']) { ?>
                <span class="post-item__text"><?= $arResult['ITEM']['PREVIEW_TEXT'] ?></span>
            <?php } ?>
          <span class="post-item__bottom">
                <span class="post-item__cost">
                  <span class="icon icon-purse"></span>
                  <?php if (!empty($arResult['ITEM']['PROPERTIES']['COST']) && (int)$arResult['ITEM']['PROPERTIES']['COST'] > 0) { ?>
                      <?= number_format($arResult['ITEM']['PROPERTIES']['COST'], '0', '', ' ') ?> <?= GetMessage('CURRENCY') ?>
                  <?php } else { ?>
                      <?= GetMessage('FREE_ACCESS') ?>
                  <?php } ?>
                </span>
              <?php

              if(!$arResult['ITEM']['IS_FOR_SINGLE_STUDY']&&!$arResult['ITEM']['ALREADY_ENROLLED']){
                  if ($arResult['ITEM']['HAS_FREE_PLACES']) { ?>
                      <span class="post-item__numbers"><?= GetMessage('FREE_PLACES') ?> - <?= $arResult['ITEM']['FREE_PLACES'] ?> / <span><?= $arResult['ITEM']['LIMIT'] ?></span></span>
                  <?php } else {?>
                      <?php if(!$arResult['ITEM']['LIMIT']):?>
                        <span class="post-item__numbers"><?= GetMessage('NO_FREE_PLACES') ?></span>
                      <?php endif;?>
                  <?php
                  }
              }?>
          </span>
        </span>
    </a>
</div>
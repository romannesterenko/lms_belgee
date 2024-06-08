<?php use Helpers\DateHelper;
use Teaching\SheduleCourses;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);?>

<?php if(count($arResult['UPCOMING_ITEMS'])>0){?>
    <h3 class="h3"><?=GetMessage('UPCOMING_COURSES_TITLE')?></h3>
    <div class="upcoming-courses aside-block aside-block--border">
        <?php foreach ($arResult['UPCOMING_ITEMS'] as $arItem){?>
            <div class="upcoming-course">
                <a href="/shedules/<?=$arItem['ID']?>/">
                    <span class="icon icon-calendar"></span>
                    <span class="upcoming-course__content">
                    <span class="upcoming-course__top">
                      <span class="upcoming-course__date"><?= DateHelper::getHumanDate($arItem['PROPERTIES']['BEGIN_DATE'], 'd F')?></span>
                        <?php if($arItem['PROPERTIES']['LIMIT']==0||empty($arItem['PROPERTIES']['LIMIT'])){?>
                        <?php }else{?>
                            <span class="upcoming-course__places"><span><?= SheduleCourses::getFreePlaces($arItem['ID'], $arItem['PROPERTIES']['LIMIT'])?></span> / <?=$arItem['PROPERTIES']['LIMIT']?> <?=GetMessage('UPCOMING_COURSES_PLACES')?></span>
                        <?php }?>
                    </span>
                    <span class="upcoming-course__title"><?=$arItem['NAME']?></span>
                  </span>
                </a>
            </div>
        <?php }?>
    </div>
<?php }?>

<?php if(count($arResult['ALREADY_ITEMS'])>0){?>
    <h3 class="h3"><?=GetMessage('UPCOMING_ALREADY_TITLE')?></h3>
    <div class="upcoming-courses aside-block aside-block--border">
        <?php foreach ($arResult['ALREADY_ITEMS'] as $arItem){?>
        <div class="upcoming-course">
            <a href="/courses/<?=$arItem['COURSE']['CODE']?>/">
                <span class="icon icon-calendar"></span>
                <span class="upcoming-course__content">
                        <span class="upcoming-course__top">
                          <span class="upcoming-course__date"><?= DateHelper::getHumanDate($arItem['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd F')?></span>
                          <span class="upcoming-course__places"><span><?= SheduleCourses::getFreePlaces($arItem['UF_SHEDULE_ID'], $arItem['SCHEDULE']['PROPERTIES']['LIMIT'])?></span> / <?=$arItem['SCHEDULE']['PROPERTIES']['LIMIT']?> <?=GetMessage('UPCOMING_COURSES_PLACES')?></span>
                        </span>
                        <span class="upcoming-course__title"><?=$arItem['COURSE']['NAME']?></span>
                      </span>
            </a>
        </div>
        <?php }?>
        <?php /*<div class="upcoming-course">
            <a href="">
                <span class="icon icon-calendar"></span>
                <span class="upcoming-course__content">
                        <span class="upcoming-course__top">
                          <span class="upcoming-course__date">20 ноября</span>
                          <span class="upcoming-course__status offline">Offine</span>
                        </span>
                        <span class="upcoming-course__title">«Рестайл версия Atlas Pro,
                          основые отличия»</span>
                      </span>
            </a>
        </div>
        <div class="upcoming-course">
            <a href="">
                <span class="icon icon-calendar"></span>
                <span class="upcoming-course__content">
                        <span class="upcoming-course__top">
                          <span class="upcoming-course__date">28 ноября</span>
                          <span class="upcoming-course__status online">Online</span>
                        </span>
                        <span class="upcoming-course__title">«Правила работы
                          с клиентами корпсегмента»</span>
                      </span>
            </a>
        </div>*/?>
    </div>
<?php }?>

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
$this->setFrameMode(true);
?>
<div class="course-container">

    <h2 class="h2"><?=GetMessage('MAIN_MY_NEEDED_COURSES_TITLE')?></h2>
    <div class="content-block">
        <div class="course-block">
            <?php foreach ($arResult['ITEMS'] as $item){?>
            <div class="course-item">
                <span class="course-item__date">
                  <span class="icon"><span class="icon-calendar"></span> </span>
                  <?=$item['PROPERTIES']['BEGIN_DATE']['VALUE']?><?=$item['PROPERTIES']['END_DATE']['VALUE']?"-".$item['PROPERTIES']['END_DATE']['VALUE']:""?>
                </span>
                <span class="course-item__status <?=strtolower($item['PROPERTIES']['COURSE_FORMAT']['VALUE'])?>"><?=$item['PROPERTIES']['COURSE_FORMAT']['VALUE']?></span>
                <span class="course-item__text">
                    <a href="/courses/<?=$item['CODE']?>/"><?=$item['NAME']?></a>
                </span>
            </div>
            <?php }?>
        </div>
        <div class="btn-center margin">
            <a href="/cabinet/completed_courses/" class="btn "><?=GetMessage('MAIN_MY_COMPLETED_COURSES_BUTTON_TITLE')?></a>
        </div>
    </div>
</div>

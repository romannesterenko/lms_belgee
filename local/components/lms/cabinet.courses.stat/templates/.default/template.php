<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<div class="content-block content-block--margin">
    <h4 class="h4"><?=GetMessage('COURSES_STAT_TITLE')?></h4>
    <div class="stats-course">
        <div class="stat-course">
            <?=GetMessage('COURSES_STAT_COMPLETED_COURSES')?>
            <span><a href="/cabinet/completed_courses/"><?=$arResult['COMPLETED']?></a></span>
        </div>
        <div class="stat-course">
            <?=GetMessage('COURSES_STAT_COMMON_SCORE')?>
            <span><a href="/cabinet/completed_courses/"><?=$arResult['COMMON_SCORE']?></a></span>
        </div>
        <div class="stat-course">
            <?=GetMessage('COURSES_STAT_LEFT_COURSES')?>
            <span><a href="/courses/new/"><?=$arResult['LEFT_COURSES']?></a></span>
        </div>
        <div class="stat-course">
            <?=GetMessage('COURSES_STAT_NEW_COURSES')?>
            <span><a href="/courses/assigned/"><?=$arResult['NEED_COURSES']?></a></span>
        </div>
    </div>
</div>

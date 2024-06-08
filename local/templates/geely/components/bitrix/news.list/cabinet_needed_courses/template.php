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

<h3 class="h3 center"><?=GetMessage('CABINET_NEEDED_COURSES_TITLE')?></h3>

<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack" id="table-2">
        <thead class="thead-dark">
        <tr>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_COURSE')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_TYPE')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_COUNT_PARTICIPANTS')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_LIMITATION')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_DATE')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_TEST')?></th>
            <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_ENROLL')?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['ITEMS'] as $item){
            $APPLICATION->IncludeComponent("lms:course.card",
                "table_row",
                array(
                    "COURSE_ID" => $item['ID'],
                ),
                false
            );
        }?>
        </tbody>
    </table>
    <div class="content-show-link">
        <a href="/courses/assigned/">
            <?=GetMessage('CABINET_NEEDED_COURSES_TR_COURSE')?>
            <span class="icon icon-arrow-link"></span>
        </a>
    </div>
</div>

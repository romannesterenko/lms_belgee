<?php use Bitrix\Main\Localization\Loc;

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
$this->setFrameMode(true);?>
<div class="table-block">
    <?php global $needed_courses_filter;
    if(check_full_array($needed_courses_filter['ID'])){?>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-2">
        <thead class="thead-dark">
            <tr>
                <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_COURSE')?></th>
                <th>Статус</th>
                <th>Баллы</th>
                <th><?=GetMessage('CABINET_NEEDED_COURSES_TR_TYPE')?></th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($arResult['ITEMS'] as $item){?>
                <tr>
                    <td><a href="/courses/<?=$item['CODE']?>/"><?=$item['NAME']?></a></td>
                    <td><?=$item['STATUS']?></td>
                    <td><?=$item['POINTS']?$item['POINTS'].$item['MAX_POINTS']:'-'?></td>
                    <td>
                        <span class="online"> <?=$item['PROPERTIES']['COURSE_FORMAT']['VALUE']?></span>
                    </td>
                </tr>

                <?php
                /*$APPLICATION->IncludeComponent("lms:course.card",
                    "table_row_cabinet",
                    array(
                        "COURSE_ID" => $item['ID'],
                    ),
                    false
                );*/?>
            <?php }?>
        </tbody>
    </table>
    <?php } else {?>
        <p><?= Loc::getMessage('NO_COURSES') ?></p>
    <?php }?>
</div>

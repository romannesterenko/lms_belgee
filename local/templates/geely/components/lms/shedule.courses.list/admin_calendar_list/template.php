<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $APPLICATION, $USER;
?>
<h3 class="h3 center"><?=GetMessage("LIST_BLOCK_TITLE")?></h3>

<div class="content-block content-block--margin">
    <div class="timetable-course__head">
        <div class="select-block">
            <div class="select select--custom2">
                <select class="select2 roles_list_select">
                    <option value="0"><?=GetMessage("SELECT")?></option>
                    <?php foreach ($arResult['ROLES_SELECT'] as $id=> $name){?>
                        <option value="<?=$id?>"<?=$id==$arParams['FOR_ROLE']?' selected':''?>><?=$name?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="select-block">
            <div class="select  select--custom2">
                <select class="select2 roles_month_select">
                    <?php foreach ($arResult['MONTH_SELECT'] as $key => $month){?>
                        <option value="<?=$key?>"<?=$key==$arParams['MONTH'].'.'.$arParams['YEAR']?' selected':''?>><?=$month?></option>
                    <?php }?>
                </select>
            </div>
        </div>
    </div>

    <div class="timetable-content courses_ajax">
        <?php if($_REQUEST['ajax']=='Y')
            $APPLICATION->RestartBuffer();
        if(count($arResult['ITEMS'])==0){?>
            <div class="timetable-item">Курсов нет</div>
        <?}else{
            foreach ($arResult['ITEMS'] as $item){
                $APPLICATION->IncludeComponent("lms:course.card",
                    "list",
                    array(
                        "COURSE_ID" => $item,
                    ),
                    false
                );
            }
        }
        if($_REQUEST['ajax']=='Y')
            die();?>
    </div>
    <div class="btn-center margin">
        <a href="/courses/" class="btn "><?=GetMessage('SHOW_MORE')?></a>
    </div>
</div>


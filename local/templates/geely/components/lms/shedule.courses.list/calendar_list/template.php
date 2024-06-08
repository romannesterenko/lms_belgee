<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $APPLICATION, $USER;?>
<h2 class="h2"><?=GetMessage("LIST_BLOCK_TITLE")?></h2>

<div class="content-block">
    <div class="timetable-head">
        <?php /*
        <div class="select-block select-block--long">
            <label for=""><?=GetMessage("LIST_TEACHING_FOR")?></label>
            <div class="select">
                <select class="select2 roles_list_select">
                    <option value="0"><?=GetMessage("SELECT")?></option>
                    <?php foreach ($arResult['ROLES_SELECT'] as $id=> $name){
                        $for_role = is_array($arParams['FOR_ROLE'])?$arParams['FOR_ROLE']:[$arParams['FOR_ROLE']]?>
                        <option value="<?=$id?>"<?=in_array($id, $for_role)?' selected':''?>><?=$name?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        */?>
        <div class="select-block select-block--long">
            <label for=""><?=GetMessage("LIST_MONTH_SELECT_TITLE")?></label>
            <div class="select">
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
            <div class="timetable-item">Курсов для записи нет</div>
        <?php } else {
            /*if(false){
                foreach ($arResult['SHEDULES'] as $item) {
                    $APPLICATION->IncludeComponent("lms:course_shedule.card",
                        "list",
                        array(
                            "SHEDULE_ID" => $item,
                        ),
                        false
                    );
                }
            } else {*/
                foreach ($arResult['ITEMS'] as $item) {
                    $APPLICATION->IncludeComponent("lms:course.card",
                        "list",
                        array(
                            "COURSE_ID" => $item,
                        ),
                        false
                    );
                }
            /*}*/
        }
        if($_REQUEST['ajax']=='Y')
            die();?>
    </div>
    <div class="btn-center margin">
        <a href="/courses/" class="btn "><?=GetMessage('SHOW_MORE')?></a>
    </div>
</div>


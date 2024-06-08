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
$this->setFrameMode(true);?>
<div class="content">
    <?php if($APPLICATION->GetCurPage()=="/courses/"){?>
    <h3 class="h3">Список курсов</h3>
    <div class="content-block">
        <div class="form-control" style="margin-bottom: 15px;">
            <div class="select">
                <select class="select2 select_courses_category">
                    <option value="all">Все курсы</option>
                    <option value="op"<?=$_REQUEST['cat']=='op'?" selected":""?>>Отдел продаж</option>
                    <option value="ppo"<?=$_REQUEST['cat']=='ppo'?" selected":""?>>Послепродажное обслуживание</option>
                </select>
            </div>
        </div>
    <?php } else {?>
        <div class="content-block">
    <?php }?>
        <div class="materials-block courses_list">
            <div class="materials-block__content">
                <?php if($_REQUEST['ajax_load_more']=='Y')
                    $APPLICATION->RestartBuffer();?>
                <?php foreach($arResult["ITEMS"] as $arItem):?>
                    <?php $APPLICATION->IncludeComponent("lms:course.card",
                        "card",
                        array(
                            "COURSE_ID" => $arItem['ID'],
                        ),
                        false
                    );?>
                <?php endforeach;?>
                <?php if($_REQUEST['ajax_load_more']=='Y')
                    die();?>
            </div>
            <?=$arResult["NAV_STRING"]?>
        </div>
    </div>
</div>

<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
?>
<h2 class="h2"><?=GetMessage("LIST_BLOCK_TITLE")?></h2>

<div class="content-block">

    <div class="timetable-head">
        <div class="select-block select-block--long">
            <label for=""><?=GetMessage("LIST_TEACHING_FOR")?></label>
            <div class="select">
                <select class="select2 roles_list_select">
                    <option value="0"><?=GetMessage("SELECT")?></option>
                    <?foreach ($arResult['ROLES_SELECT'] as $id=>$name){?>
                        <option value="<?=$id?>"><?=$name?></option>
                    <?}?>
                </select>
            </div>
        </div>
        <div class="select-block select-block--long">
            <label for=""><?=GetMessage("LIST_MONTH_SELECT_TITLE")?></label>
            <div class="select">
                <select class="select2 roles_month_select">
                    <?foreach ($arResult['MONTH_SELECT'] as $key => $month){?>
                        <option value="<?=$key?>"><?=$month?></option>
                    <?}?>
                </select>
            </div>
        </div>
    </div>

    <div class="timetable-content courses_ajax">
        <?if($_REQUEST['ajax']=='Y')
            $APPLICATION->RestartBuffer();
        foreach ($arResult['ITEMS'] as $item){?>
            <div class="timetable-item">
                <a href="">
                  <span class="timetable-item__image">
                    <img src="<?=CFile::GetPath($item['PROPERTY_COURSE_ITEM']['PREVIEW_PICTURE'])?>" alt="">
                    <span class="timetable-item__category"> <span class="icon icon-check"></span>Обязательный курс</span>
                  </span>
                    <span class="timetable-item__content">
                    <span class="timetable-item__top">
                      <span class="timetable-item__date"><span class="icon icon-calendar"></span><?=$item['PROPERTY_BEGIN_DATE_VALUE']?> - <?=$item['PROPERTY_END_DATE_VALUE']?><?/*28 июня - 12 июля 2022*/?></span>
                      <span class="timetable-item__status<?=$item['IS_ONLINE']?'':' offline'?>"><?=$item['IS_ONLINE']?'Online':'Offline'?></span>
                    </span>
                    <span class="timetable-item__title"><?=$item['PROPERTY_COURSE_ITEM']['NAME']?></span>
                    <span class="timetable-item__text"><?=$item['PROPERTY_COURSE_ITEM']['PREVIEW_TEXT']?></span>
                    <span class="timetable-item__bottom">
                        <span class="timetable-item__cost">
                            <span class="icon icon-purse"></span>
                            <?if(!empty($item['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'])&&(int)$item['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE']>0){?>
                                <?=number_format($item['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'], '0', '', ' ')?> руб.
                            <?}else{?>
                                <?=GetMessage('FREE_ACCESS')?>
                            <?}?>
                        </span>
                      <span class="timetable-item__numbers"><?=GetMessage('FREE_PLACES')?> - <?=$item['FREE_PLACES']?> / <span><?=$item['PROPERTY_COURSE_ITEM']['PROPERTY_LIMIT_VALUE']??30?></span></span>
                    </span>
                    <div class="timetable-item__discription"><?=GetMessage('FOR')?><?=$item['FOR_ROLES']?></div>
                  </span>
                </a>
                <a href="" class="btn"><?=GetMessage('ENROLL')?></a>
            </div>
        <?}
        if($_REQUEST['ajax']=='Y')
            die();?>
    </div>
    <div class="btn-center margin">
        <a href="/courses/" class="btn "><?=GetMessage('SHOW_MORE')?></a>
    </div>
</div>


<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER, $APPLICATION;
if (check_full_array($arResult['ITEM'])){?>
    <div class="timetable-item list_item_card" data-id="<?=$arResult['ITEM']['ID']?>">
        <?php if($_REQUEST['ajax_card']=='Y')
            $APPLICATION->RestartBuffer();?>
            <a href="<?=$arResult['ITEM']['DETAIL_PAGE_URL']?>">
                <span class="timetable-item__image">
                    <img src="<?=$arResult['ITEM']['PREVIEW_PICTURE']>0?CFile::GetPath($arResult['ITEM']['PREVIEW_PICTURE']):SITE_TEMPLATE_PATH.'/images/img-3.jpg'?>" alt="">
                    <?php if($arResult['ITEM']['NEED_LABEL']){?>
                        <span class="timetable-item__category"><span class="icon icon-check"></span><?=$arResult['ITEM']['LABEL']?></span>
                    <?php }?>
                </span>
                <span class="timetable-item__content">
                    <span class="timetable-item__top">
                        <?php /*if($arResult['ITEM']['HAS_DATES']){?>
                            <span class="timetable-item__date">
                            <span class="icon icon-calendar"></span>
                            <?=$arResult['ITEM']['BEGIN_DATE']?> - <?=$arResult['ITEM']['END_DATE']?>
                        </span>
                        <?}*/?>
                        <span class="timetable-item__status <?=strtolower($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT'])?>"><?=$arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']?></span>
                    </span>
                    <span class="timetable-item__title"><?=$arResult['ITEM']['NAME']?></span>
                    <span class="timetable-item__text"><?=$arResult['ITEM']['PREVIEW_TEXT']?></span>
                    <span class="timetable-item__bottom">
                        <span class="timetable-item__cost">
                            <span class="icon icon-purse"></span>
                            <?php if(!empty($arResult['ITEM']['PROPERTIES']['COST'])&&(int)$arResult['ITEM']['PROPERTIES']['COST']>0){
                                if(\Models\User::needToHidePrice()){?>
                                    Курс платный
                                <?php } else {?>
                                    <?=number_format($arResult['ITEM']['PROPERTIES']['COST'], '0', '', ' ')?> <?=GetMessage('CURRENCY')?>
                                <?php }?>
                            <?php }else{?>
                                <?=GetMessage('FREE_ACCESS')?>
                            <?php }?>
                        </span>
                        <?php
                        if(check_full_array($arResult['SCHEDULES_TO_ENROLL'])&&count($arResult['SCHEDULES_TO_ENROLL'])>1){

                        }else{
                        if($arResult['ITEM']['HAS_FREE_PLACES']){?>
                            <span class="timetable-item__numbers"><?=GetMessage('FREE_PLACES')?> - <?=$arResult['ITEM']['FREE_PLACES']?> / <span><?=$arResult['ITEM']['LIMIT']?></span></span>
                        <?php } else{?>
                            <?php if(!$arResult['ITEM']['LIMIT']):?><span class="timetable-item__numbers"><?= GetMessage('NO_FREE_PLACES') ?></span><?php endif;?>
                        <?php }
                        }?>
                    </span>
                    <?php if($arResult['ITEM']['PROPERTIES']['FOR_ROLES']&&count($arResult['ITEM']['PROPERTIES']['FOR_ROLES'])>0){?>
                        <div class="timetable-item__discription"><?=GetMessage('FOR')?><?=strtolower(implode(', ', $arResult['ITEM']['PROPERTIES']['FOR_ROLES']))?></div>
                    <?php }?>
                    <?php if($arResult['SCHEDULES_TO_ENROLL']!==[]){?>
                        <span class="timetable-item__bottom mt-10">
                            <span class="timetable-item__cost"><?= Loc::getMessage('COURSE_DATES') ?></span>
                        </span>
                        <span class="timetable-item__text" style="margin-bottom: 50px;">
                            <?php foreach($arResult['SCHEDULES_TO_ENROLL'] as $key => $schedule_enroll){
                                $dates = [];
                                $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['BEGIN_DATE']));
                                $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['END_DATE']));
                                $dates = array_unique($dates);
                                ?>
                                <?=$key==0?'':', '?><?=count($dates)>1? Loc::getMessage('FROM').$dates[0].Loc::getMessage('YEAR').Loc::getMessage('TO').$dates[1].Loc::getMessage('YEAR'):$dates[0].Loc::getMessage('YEAR')?>
                            <?php }?>
                        </span>
                    <?php }?>
                </span>
            </a>
        <?php if($arResult['ITEM']['REGISTER_BUTTON']['NEED_SHOW']){?>
            <a href="javascript:void(0)" class="btn detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=GetMessage('ENROLL')?></a>
        <?php }?>
        <?php if($_REQUEST['ajax_card']=='Y')
            die();?>
    </div>
<?php }?>
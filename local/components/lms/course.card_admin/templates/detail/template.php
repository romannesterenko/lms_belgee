<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

use Bitrix\Main\Localization\Loc;
use Helpers\StringHelpers;
use Teaching\MaterialsFiles;

$this->setFrameMode(true);
global $USER, $APPLICATION;
?>
<div class="content-section">
    <h1 class="h1"><?=$arResult['ITEM']['NAME']?></h1>
    <?php if((int)$_REQUEST['schedule_from_calendar']>0){?>
        <input type="hidden" id="need_shedule_in_select" value="<?=(int)$_REQUEST['schedule_from_calendar']?>">
    <?php }?>
    <div class="content-block">
        <div class="course-main-block">
            <div class="course-main">
                <div class="course-main__image">
                    <img src="<?=$arResult['ITEM']['PREVIEW_PICTURE']>0?CFile::GetPath($arResult['ITEM']['PREVIEW_PICTURE']):SITE_TEMPLATE_PATH.'/images/img-3.jpg'?>" alt="">
                </div>
                <div class="course-main__content">
                    <div class="course-main__information">
                        <?php if(is_array($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE'])&&count($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE'])>0){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title">Даты проведения:</span>
                                <p>
                                    <?php foreach($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE'] as $key => $schedule_enroll){
                                        $dates = [];
                                        $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['BEGIN_DATE']));
                                        $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['END_DATE']));
                                        $dates = array_unique($dates);
                                        echo $key==0?'':', '?><?=count($dates)>1?$dates[0].Loc::getMessage('YEAR')." - ".$dates[1].Loc::getMessage('YEAR'):$dates[0]." г."?>
                                    <?php }?>
                                </p>
                            </div>
                        <?php } else {?>
                            <?php if(!empty($arResult['ITEM']['BEGIN_DATE'])){?>
                                <div class="course-main__info-item">
                                    <span class="course-main__info-item-title"><?=Loc::getMessage('DATE_TITLE')?></span>
                                    <span class="course-main__info-item-date"><?=$arResult['ITEM']['BEGIN_DATE']?> </span>
                                </div>
                            <?php }?>
                            <?php if(!empty($arResult['ITEM']['BEGIN_TIME'])){?>
                                <div class="course-main__info-item">
                                    <span class="course-main__info-item-title"><?=Loc::getMessage('BEGIN_TITLE')?></span>
                                    <span class="course-main__info-item-time-start"><?=$arResult['ITEM']['BEGIN_TIME']?></span>
                                </div>
                            <?php }?>
                        <?php }?>
                        <?php if($arResult['ITEM']['IS_FOR_SINGLE_STUDY']){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=Loc::getMessage('SINGLE_STUDY_TITLE')?></span>
                                <span class="course-main__info-item-duration"><?=Loc::getMessage('SINGLE_STUDY_VALUE')?></span>
                            </div>
                        <?php }?>
                        <?php if(!empty($arResult['ITEM']['PROPERTIES']['DURING'])){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=Loc::getMessage('DURATION_TITLE')?></span>
                                <span class="course-main__info-item-duration"><?=$arResult['ITEM']['PROPERTIES']['DURING']?> <?= StringHelpers::plural($arResult['ITEM']['PROPERTIES']['DURING'], [Loc::getMessage('HOUR'), Loc::getMessage('HOUR_2'), Loc::getMessage('HOUR_3')])?></span>
                            </div>
                        <?php }?>
                        <div class="course-main__info-item">
                            <span class="course-main__info-item-title"><?=Loc::getMessage('FORMAT_TITLE')?></span>
                            <span class="course-main__info-item-format <?=strtolower($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT'])?>"><?=$arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']?></span>
                        </div>
                        <?php if($arResult['ITEM']['PROPERTIES']['FOR_ROLES']&&count($arResult['ITEM']['PROPERTIES']['FOR_ROLES'])>0){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=Loc::getMessage('FOR_TITLE')?></span>
                                <p><?php //Loc::getMessage('FOR')?> <?=strtolower(implode(', ', $arResult['ITEM']['PROPERTIES']['FOR_ROLES']))?> </p>
                            </div>
                        <?php }?>
                    </div>
                    <?php if($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']=="Offline"):?>
                        <div class="course-main__contacts">
                        <div class="course-main__adress">
                            <div class="course-main__city">
                                <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/location-empty.svg" alt=""></span>
                                <?=$arResult['ITEM']['PROPERTIES']['CITY']?>
                            </div>
                            <?=$arResult['ITEM']['PROPERTIES']['ADDRESS']?>
                        </div>
                    </div>
                    <?php endif;?>
                </div>
            </div>

            <div class="course-main-info course-main-info--third" data-id="<?=$arResult['ITEM']['ID']?>">
                <?php if($_REQUEST['load_ajax']=='enrolled_data')
                    $APPLICATION->RestartBuffer();?>
                <div class="course-main-info-item">
                    <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/purse2.svg" alt=""></span>
                    <span class="course-main-info-item__title"><?= Loc::getMessage('COST_TITLE')?></span>
                    <?php if((int)$arResult['ITEM']['PROPERTIES']['COST']>0){?>
                        <span class="course-main-info-item__numbers"><?=number_format((float)$arResult['ITEM']['PROPERTIES']['COST'], 0, ',', ' ')?> <small><?=Loc::getMessage('CURRENCY')?></small> </span>
                    <?php }else{?>
                        <span class="course-main-info-item__numbers"><?=Loc::getMessage('FREE_ACCESS')?></span>
                    <?php }?>
                </div>
                <?php if($arResult['ITEM']['INFO']){?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> <?=$arResult['ITEM']['INFO']?></span>
                <?php }


                ?>
                <?php if($arResult['ITEM']['IS_FOR_SINGLE_STUDY']){?>
                    <?php if($arResult['ITEM']['WAS_STARTED']){?>
                        <a href="<?=$arResult['ITEM']['COMPLETION_LINK']?>" class="btn btn--reverse"><?= Loc::getMessage('TO_PROCESS') ?></a>
                    <?php } elseif($arResult['ITEM']['IS_COMPLETED_COURSE']){ ?>
                        <?php if(\Models\Course::isIgnoreStatus($arResult['ITEM']['ID'])){?>
                            <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                        <?php } else { ?>
                            <a href="<?=$arResult['ITEM']['COMPLETION_LINK']?>" class="btn btn--reverse"><?= Loc::getMessage('VIEW') ?></a>
                        <?php }?>
                    <?php } else {?>
                        <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                    <?php }?>
                    <?php if($arResult['USER']['HAS_RIGHTS_TO_SET_COURSE']&&$arResult['ITEM']['FOR_SETTING']){?>
                        <a href="javascript:void(0)" class="btn btn--reverse set_course_to_employee_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('SET_TO_EMPLOYEE')?></a>
                    <?php }?>
                    <?php if($arResult['USER']['HAS_RIGHTS_TO_ENROLL_EMPLOYEE']){?>
                        <a href="javascript:void(0)" class="btn btn--reverse employee_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL_EMPLOYEE')?></a>
                    <?php }?>
                <?php }

                ?>
                <?php if($_REQUEST['load_ajax']=='enrolled_data')
                    die();?>
            </div>
        </div>
        <div class="text-content text-content--border">
            <p>
            <?=$arResult['ITEM']['PROPERTIES']['TEXT_BLOCK_1']['TEXT']?>
            </p>
            <?php if(!empty($arResult['ITEM']['PROPERTIES']['TEXT_SLIDER'])&&count($arResult['ITEM']['PROPERTIES']['TEXT_SLIDER'])>0){?>
                <div class="content-slider owl-carousel">
                    <?php foreach ($arResult['ITEM']['PROPERTIES']['TEXT_SLIDER'] as $img_id){?>
                        <div class="item"><img src="<?=CFile::GetPath($img_id['VALUE'])?>" alt=""></div>
                    <?php }?>
                </div>
            <?php }?>
            <p>
            <?=$arResult['ITEM']['PROPERTIES']['TEXT_BLOCK_2']['TEXT']?>
            </p>

            <?php if(!empty($arResult['ITEM']['PROPERTIES']['SCHEDULE'])&&count($arResult['ITEM']['PROPERTIES']['SCHEDULE'])>0){?>
                <div class="timetable-event">
                    <h2 class="h2"><?=Loc::getMessage('SCHEDULE_TITLE')?></h2>
                    <div class="timetable-event__content">
                        <?php
                        foreach ($arResult['ITEM']['PROPERTIES']['SCHEDULE'] as $key => $value){?>
                            <div class="timetable-event-item">
                                <div class="timetable-event-item__time"><?=$value['DESCRIPTION']?></div>
                                <div class="timetable-event-item__content"><?=$value['VALUE']['TEXT']?></div>
                            </div>
                        <?php }?>
                    </div>
                </div>
            <?php }?>
        </div>
        <?php if(!empty($arResult['ITEM']['PROPERTIES']['TRAINERS'])&&count($arResult['ITEM']['PROPERTIES']['TRAINERS'])>0){?>
            <h2 class="h2"><?=Loc::getMessage('MAIN_EVENT_SPEAKER')?></h2>
            <div class="trainers trainers--border-none">
                <div class="trainer-item">
                    <div class="trainer-item__avatar"><img src="<?=(int)$arResult['MAIN_SPEAKER']['PREVIEW_PICTURE']>0?CFile::GetPath($arResult['MAIN_SPEAKER']['PREVIEW_PICTURE']):'/local/templates/geely/images/default_avatar.svg'?>" alt=""></div>
                    <div class="trainer-item__content">
                        <span class="trainer-item__name"><?=$arResult['MAIN_SPEAKER']['NAME']?></span>
                        <p><?=$arResult['MAIN_SPEAKER']['PREVIEW_TEXT']?></p>
                    </div>
                </div>
                <?php if(count($arResult['SPEAKERS'])>1){?>
                    <h2 class="h2"><?=Loc::getMessage('EVENT_SPEAKERS')?></h2>
                    <?php foreach ($arResult['SPEAKERS'] as $key=> $value){
                        if($key==0)
                            continue;?>
                        <div class="trainer-item">
                            <div class="trainer-item__avatar"><img src="<?=(int)$value['PREVIEW_PICTURE']>0?CFile::GetPath($value['PREVIEW_PICTURE']):'/local/templates/geely/images/default_avatar.svg'?>" alt=""></div>
                            <div class="trainer-item__content">
                                <span class="trainer-item__name"><?=$value['NAME']?></span>
                                <p><?=$value['PREVIEW_TEXT']?></p>
                            </div>
                        </div>
                    <?php }?>
                <?php }?>
            </div>
        <?php }?>
    </div>
</div>
<?php if(check_full_array($arResult['ITEM']['PROPERTIES']['NEED_COURSES_BEFORE'])){
    $need_courses = false;
    $need_courses_array = [];
    $compls = new \Teaching\CourseCompletion();
    foreach ($arResult['ITEM']['PROPERTIES']['NEED_COURSES_BEFORE'] as $course_before){
        if((int)$course_before['VALUE']>0&&!$compls->isCompleted($course_before['VALUE'])){
            $need_courses_array[] = \Models\Course::find((int)$course_before['VALUE'], ['ID', 'NAME', 'CODE']);
            $need_courses = true;
        }
    }
    if($need_courses&&check_full_array($need_courses_array)){?>
    <div class="content-section">
        <h2 class="h2"><?=GetMessage('NEED_COURSES_TITLE')?></h2>
        <div class="content-block">
            <div class="course-list">
                <?php foreach ($need_courses_array as $crs){?>
                    <div class="course-list-item">
                        <span class="course-list-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/education-icon.svg" alt=""></span>
                        <span class="course-list-item__title"><a href="/courses/<?=$crs['CODE']?>/">«<?=$crs['NAME']?>»
                    </a></span>
                        <span class="course-list-item__btn"><a href="/courses/<?=$crs['CODE']?>/"><span class="icon icon-arrow-link"></span></a></span>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <?php }?>
<?php }?>
<?php if(!empty($arResult['MATERIALS_FILES'])&&count($arResult['MATERIALS_FILES'])>0){?>
<div class="content-section">
    <h2 class="h2"><?=Loc::getMessage('MATERIALS_TITLE')?></h2>
    <div class="content-block">
        <div class="materials">
            <?php foreach ($arResult['MATERIALS_FILES'] as $file){?>
                <a href="<?=$file['FILE_INFO']['SRC']?>" download="<?=$file['FILE_INFO']['ORIGINAL_NAME']?>" class="material-download-item">
                <span class="material-download-item__title"><?=$file['NAME']?></span>
                <span class="material-download-item__icon">
                    <span class="icon">
                        <img src="<?=$file['FILE_INFO']['FILE_ICON']?>" alt="">
                    </span>
                    (<?= MaterialsFiles::resizeBytes($file['FILE_INFO']['FILE_SIZE']);?> <?=Loc::getMessage('MB')?>)
                </span>
                </a>
            <?php }?>
        </div>
    </div>
</div>
<?php }?>
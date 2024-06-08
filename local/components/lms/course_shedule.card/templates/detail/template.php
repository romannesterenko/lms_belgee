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
    <h1 class="h1"><?=$arResult['ITEM']['SCHEDULE']['NAME']??$arResult['ITEM']['NAME']?></h1>
    <?php if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE']){
        if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE']&&$arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE']!=$arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE']){?>
            <h2 class="h2"><?php echo \Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE'])?> - <?php echo \Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE'])?></h2>
        <?php } else {?>
            <h2 class="h2"><?php echo \Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE'])?></h2>
        <?php }?>
    <?php }?>
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
                        <?php if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE']) {
                            if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE']&&$arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE']!=$arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE']){?>
                                <div class="course-main__info-item">
                                    <span class="course-main__info-item-title"><?=Loc::getMessage('DATE_TITLE')?></span>
                                    <span class="course-main__info-item-date"><?=\Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd F')?> - <?=\Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_DATE'], 'd F')?></span>
                                </div>
                            <?php } else {?>
                                <div class="course-main__info-item">
                                    <span class="course-main__info-item-title"><?=Loc::getMessage('DATE_TITLE')?></span>
                                    <span class="course-main__info-item-date"><?=\Helpers\DateHelper::getHumanDate($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd F')?></span>
                                </div>
                            <?php }?>
                        <?php }?>
                        <?php if(!empty($arResult['ITEM']['BEGIN_TIME'])&&$arResult['ITEM']['BEGIN_TIME']!='00:00'){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=Loc::getMessage('BEGIN_TITLE')?></span>
                                <span class="course-main__info-item-time-start"><?=$arResult['ITEM']['BEGIN_TIME']?></span>
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

                        <?php if($arResult['ITEM']['PROPERTIES']['FOR_ROLES_MUST']&&count($arResult['ITEM']['PROPERTIES']['FOR_ROLES_MUST'])>0){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><b><?=Loc::getMessage('FOR_TITLE_MUST')?></b></span>
                                <p><?=strtolower(implode(', ', $arResult['ITEM']['PROPERTIES']['FOR_ROLES_MUST']))?> </p>
                            </div>
                        <?php }?>
                        <?php if($arResult['ITEM']['PROPERTIES']['FOR_ROLES']&&count($arResult['ITEM']['PROPERTIES']['FOR_ROLES'])>0){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=Loc::getMessage('FOR_TITLE')?></span>
                                <p><?=strtolower(implode(', ', $arResult['ITEM']['PROPERTIES']['FOR_ROLES']))?> </p>
                            </div>
                        <?php }?>
                    </div>
                    <?php if($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']=="Offline"):?>
                        <div class="course-main__contacts">
                            <?php if(!empty($arResult['ITEM']['SCHEDULE']['PROPERTIES']['ADDRESS'])){?>
                                <div class="course-main__adress">
                                    <div class="course-main__city">
                                        <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/location-empty.svg" alt=""></span>
                                    </div>
                                    <?=$arResult['ITEM']['SCHEDULE']['PROPERTIES']['ADDRESS']?>
                                </div>
                            <?php } else {?>
                                <div class="course-main__adress">
                                    <div class="course-main__city">
                                        <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/location-empty.svg" alt=""></span>
                                        <?=$arResult['ITEM']['PROPERTIES']['CITY']?>
                                    </div>
                                    <?=$arResult['ITEM']['PROPERTIES']['ADDRESS']?>
                                </div>
                            <?php }?>
                        </div>
                    <?php endif;?>
                </div>
            </div>


            <div class="course-main-info course-main-info--third" data-id="<?=$arResult['ITEM']['SCHEDULE']['ID']?>">
                <?php if($_REQUEST['load_ajax']=='enrolled_data')
                    $APPLICATION->RestartBuffer();
                ?>
                <?php if(!$arResult['ITEM']['SENT_REQUEST']&&!$arResult['ITEM']['WAS_ENDED']&&!$arResult['ITEM']['WAS_STARTED']) {
                    if(!empty($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_REGISTRATION_DATE'])) {
                        $begin_registration_tmstp = strtotime($arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_REGISTRATION_DATE']);
                        if(time()<$begin_registration_tmstp){?>
                            <div class="course-main-info-item">
                                <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/list-icon.svg" alt=""></span>
                                <span class="course-main-info-item__title"><?=Loc::getMessage('REGISTER_AVAILABLE')?></span>
                                <span class="course-main-info-item__numbers">
                            <?php $array = explode('00:00:00', $arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_REGISTRATION_DATE']);?>
                                    <?=$array[0]?><?php if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']){?> - <?=$arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']?><?php }?></span>
                            </div>
                        <?php } else {
                            if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']){
                                $end_register_timestamp = strtotime($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']." 23:59:59");
                                if(time()<$end_register_timestamp){?>
                                    <div class="course-main-info-item">
                                        <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/list-icon.svg" alt=""></span>
                                        <span class="course-main-info-item__title"><?=Loc::getMessage('REGISTER_AVAILABLE')?></span>
                                        <span class="course-main-info-item__numbers">
                                        <?php $array = explode('00:00:00', $arResult['ITEM']['SCHEDULE']['PROPERTIES']['BEGIN_REGISTRATION_DATE']);?>
                                            <?=$array[0]?><?php if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']){?> - <?=$arResult['ITEM']['SCHEDULE']['PROPERTIES']['END_REGISTRATION_DATE']?><?php }?></span>
                                    </div>
                                <?php }
                            }
                        }?>
                    <?php }
                }?>
                <?php

                $need_cost_block_show = true;
                if (\Models\Course::isOP($arResult['ITEM']['ID']) && \Settings\Common::get('show_cost_in_op_courses')!="Y") {
                    $need_cost_block_show = false;
                }
                if($need_cost_block_show) {?>
                    <div class="course-main-info-item">
                        <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/purse2.svg" alt=""></span>
                        <span class="course-main-info-item__title"><?= Loc::getMessage('COST_TITLE')?></span>
                        <?php if((int)$arResult['ITEM']['PROPERTIES']['COST']>0){
                            if(\Models\User::needToHidePrice()){?>
                                <span class="course-main-info-item__numbers">Курс платный</span>
                            <?php } else {?>
                                <span class="course-main-info-item__numbers"><?=number_format((float)$arResult['ITEM']['PROPERTIES']['COST'], 0, ',', ' ')?> <small><?=Loc::getMessage('CURRENCY')?></small> </span>
                            <?php }?>
                        <?php }else{?>
                            <span class="course-main-info-item__numbers"><?=Loc::getMessage('FREE_ACCESS')?></span>
                        <?php }?>
                    </div>
                <?php }?>
                <?php if ( $arResult['ITEM']['WAS_ENDED'] ) {
                    ?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> Курс окончен</span>
                <?php } elseif ( $arResult['ITEM']['WAS_STARTED'] ) {
                    ?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> Курс начат</span>
                <?php }?>
                <?php if(!$arResult['ITEM']['ALREADY_ENROLLED']) {

                    if(!$arResult['ITEM']['WAS_ENDED']) {

                            if ($arResult['ITEM']['HAS_FREE_PLACES']) {

                                if (!$arResult['ITEM']['ALLOW_TO_REGISTER_BY_DATE']) { ?>
                                    <div class="course-main-info-item"><?= Loc::getMessage('REGISTRATION_CLOSED') ?></div>
                                <?php } else {

                                    if ($arResult['ITEM']['NO_LIMIT']) {?>
                                        <div class="course-main-info-item"><?= Loc::getMessage('MANY_PARTICIPANTS') ?></div>
                                    <?php } else { ?>
                                        <div class="course-main-info-item">
                                            <span class="course-main-info-item__icon"><img
                                                        src="<?= SITE_TEMPLATE_PATH ?>/images/user3.svg" alt=""></span>
                                            <span class="course-main-info-item__title"><?= Loc::getMessage('FREE_PLACES_TITLE') ?></span>
                                            <span class="course-main-info-item__numbers"> <?= $arResult['ITEM']['FREE_PLACES'] ?> <span>/ <?= $arResult['ITEM']['LIMIT'] ?></span></span>
                                        </div>
                                    <?php }
                                } ?>
                            <?php } else {?>
                                <div class="course-main-info-item">
                                            <span class="course-main-info-item__icon"><img
                                                        src="<?= SITE_TEMPLATE_PATH ?>/images/user3.svg" alt=""></span>
                                    <span class="course-main-info-item__title"><?= Loc::getMessage('FREE_PLACES_TITLE') ?></span>
                                    <span class="course-main-info-item__numbers"> <?= Loc::getMessage('NO_FREE_PLACES') ?></span>
                                </div>
                            <?php }
                        }
                }
                if(!\Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID'])){?>
                    <span class="status status--not-passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete.svg" alt=""></span> На балансе дилера недостаточно средств для записи</span>
                <?php }
                if(!$arResult['ITEM']['IS_COMPLETED_COURSE']&&(new \Teaching\CourseCompletion())->missAttemptsBySchedule($arResult['ITEM']['SCHEDULE']['ID'])){
                    $miss = true;?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> Попытки исчерпаны</span>
                <?php } else { if($arResult['ITEM']['INFO']){?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> <?=$arResult['ITEM']['INFO']?></span>
                <?php }
                }
                if($arResult['ITEM']['ALREADY_ENROLLED']&&$arResult['ITEM']['WAS_STARTED']&&$arResult['ITEM']['IS_HYBRID']){
                    if (\Models\Course::hasIncomingTest($arResult['ITEM']['ID'])){
                        if(\Models\Course::hasUncompletingIncomingTest($arResult['ITEM']['ID'])){?>
                            <a href="<?=\Teaching\Tests::generateLinkToIncomingTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти предварительный тест</a>
                        <?php } else {
                            if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['ALLOW_MAIN_TEST']=='Да') {?>
                                <a href="<?=\Teaching\Tests::generateLinkToTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти тест</a>
                            <?php } else { ?>
                                <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span>Выходное тестирование еще не разрешено</span>
                            <?php }
                        }?>
                    <?php } else {
                        if($arResult['ITEM']['SCHEDULE']['PROPERTIES']['ALLOW_MAIN_TEST']=='Да') {?>
                            <a href="<?=\Teaching\Tests::generateLinkToTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти тест</a>
                        <?php } else { ?>
                            <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span>Выходное тестирование еще не разрешено</span>
                        <?php }
                    }?>
                <?php }?>
                <?php if(\Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID']) && $arResult['ITEM']['HAS_FREE_PLACES']&&$arResult['ITEM']['REGISTER_BUTTON']['NEED_SHOW']){?>
                        <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_shedule_butt" data-course-id="<?=$arResult['ITEM']['SCHEDULE']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                <?php }?>
                <?php if($arResult['ITEM']['IS_COMPLETED_COURSE']&&!\Teaching\SheduleCourses::isExistsCheckedByCourse($arResult['ITEM']['ID'])){?>
                        <a href="<?=$arResult['ITEM']['COMPLETION_LINK']?>" class="btn btn--reverse"><?= Loc::getMessage('VIEW') ?></a>
                <?php }?>
                <?php if($arResult['ITEM']['HAS_FREE_PLACES']&&$arResult['USER']['HAS_RIGHTS_TO_SET_COURSE']&&$arResult['ITEM']['FOR_SETTING']){?>
                    <a href="javascript:void(0)" class="btn btn--reverse set_course_to_employee_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('SET_TO_EMPLOYEE')?></a>
                <?php }?>
                <?php
                if(\Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID']) && $arResult['ITEM']['ALLOW_TO_REGISTER_BY_DATE']&&$arResult['ITEM']['HAS_FREE_PLACES']&&$arResult['ITEM']['REGISTER_EMPLOYEE_BUTTON']['NEED_SHOW']&&$arResult['USER']['HAS_RIGHTS_TO_ENROLL_EMPLOYEE']){?>
                    <a href="javascript:void(0)" class="btn btn--reverse employee_shedule_enroll_butt" data-course-id="<?=$arResult['ITEM']['SCHEDULE']['ID']?>"><?=Loc::getMessage('ENROLL_EMPLOYEE')?></a>
                <?php } ?>
                <?php if($_REQUEST['load_ajax']=='enrolled_data')
                    die();?>
            </div>

            <?php if(!$arResult['ITEM']['IS_COMPLETE']&&(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM'])){?>
                <div class="course-main-info course-main-info--third">
                    <?php $teaching_admin = current(\Models\User::getTeachingAdminByUser());?>
                    <p>Курс не сдан, попытки прохождения исчерпаны. Обратитесь к <?=$teaching_admin['NAME']?> <?=$teaching_admin['LAST_NAME']?>, <?=$teaching_admin['EMAIL']?>, <?=$teaching_admin['PERSONAL_MOBILE']?> для добавления попыток</p>
                </div>
            <?php } ?>

        </div>
        <div class="text-content text-content--border">
            <p>
                <?=$arResult['ITEM']['SCHEDULE']['DETAIL_TEXT']??$arResult['ITEM']['PROPERTIES']['TEXT_BLOCK_1']['TEXT']?>
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
        <?php
        if(!empty($arResult['ITEM']['SCHEDULE']['PROPERTIES']['TRAINERS'])&&count($arResult['ITEM']['SCHEDULE']['PROPERTIES']['TRAINERS'])>0){?>
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
                <?php foreach ($need_courses_array as $crs){
                    $message = "";
                    if((new \Teaching\CourseCompletion())->missAttempts($crs['ID'])){
                        $message = ". Попытки исчерпаны";?>
                    <?php }?>
                    <div class="course-list-item">
                        <span class="course-list-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/education-icon.svg" alt=""></span>
                        <span class="course-list-item__title"><a href="/courses/<?=$crs['CODE']?>/">«<?=$crs['NAME']?>»<?=$message?>
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
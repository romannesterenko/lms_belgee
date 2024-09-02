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
$APPLICATION->SetTitle($arResult['ITEM']['NAME']);
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
                        <?php
                        //dump($arResult['ITEM']);
                        if(is_array($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE']) && count($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE'])>0){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title">Даты проведения:</span>
                                <p>
                                    <?php foreach($arResult['ITEM']['AVAILABLE_SCHEDULES_BY_DATE'] as $key => $schedule_enroll){
                                        $dates = [];
                                        $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['BEGIN_DATE']));
                                        $dates[] = date('d.m.Y', strtotime($schedule_enroll['PROPERTIES']['END_DATE']));
                                        $dates = array_unique($dates);
                                        echo $key==0?'':', '?><a href="/shedules/<?=$schedule_enroll['ID']?>/"><?=count($dates)>1?$dates[0].Loc::getMessage('YEAR')." - ".$dates[1].Loc::getMessage('YEAR'):$dates[0]." г."?></a>
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
                            <span class="course-main__info-item-format <?=strtolower($arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']??'offline')?>"><?=$arResult['ITEM']['PROPERTIES']['COURSE_FORMAT']??"Offline"?></span>
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
                        <div class="course-main__adress" style="display: flex; justify-content: left">
                            <div class="course-main__city" style="width: auto">
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
                <?php if(!$arResult['ITEM']['HAS_MANY_SHEDULES']){
                    if(!empty($arResult['ITEM']['BEGIN_REGISTRATION'])&&!empty($arResult['ITEM']['END_REGISTRATION'])){?>
                        <div class="course-main-info-item">
                            <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/list-icon.svg" alt=""></span>
                            <span class="course-main-info-item__title"><?=Loc::getMessage('REGISTER_AVAILABLE')?></span>
                            <span class="course-main-info-item__numbers">
                            <?php $array = explode('00:00', $arResult['ITEM']['BEGIN_REGISTRATION']);?>
                            <?=$array[0]?> - <?=$arResult['ITEM']['END_REGISTRATION']?></span>
                        </div>
                    <?php }
                }
                $need_cost_block_show = true;
                if (\Models\Course::isOP($arResult['ITEM']['ID']) && \Settings\Common::get('show_cost_in_op_courses')!="Y"){
                    $need_cost_block_show = false;
                }
                if ($need_cost_block_show){?>
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
            <?php /*if(!$arResult['ITEM']['IS_FOR_SINGLE_STUDY']){

                    if(!$arResult['ITEM']['HAS_MANY_SHEDULES']&&!$arResult['ITEM']['ALREADY_ENROLLED']){
                        if($arResult['ITEM']['HAS_FREE_PLACES']){
                            if(!$arResult['ITEM']['ALLOW_TO_REGISTER_BY_DATE']){?>
                                <div class="course-main-info-item"><?= Loc::getMessage('REGISTRATION_CLOSED') ?></div>
                            <?php }else{
                                if($arResult['ITEM']['NO_LIMIT']){?>
                                    <div class="course-main-info-item"><?= Loc::getMessage('MANY_PARTICIPANTS') ?></div>
                                <?php }else{?>
                                    <div class="course-main-info-item">
                                        <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/user3.svg" alt=""></span>
                                        <span class="course-main-info-item__title"><?=Loc::getMessage('FREE_PLACES_TITLE')?></span>
                                        <span class="course-main-info-item__numbers"> <?=$arResult['ITEM']['FREE_PLACES']?> <span>/ <?=$arResult['ITEM']['LIMIT']?></span></span>
                                    </div>
                                <?php }
                            }?>
                        <?php } else {?>
                            <?php if(!$arResult['ITEM']['LIMIT']):?>
                                <div class="course-main-info-item"><?= Loc::getMessage('NO_FREE_PLACES') ?></div>
                            <?php endif;?>
                        <?php }
                    }
                }*/
                $last_week_filter = ['PROPERTY_COURSE' => (int)$arResult['ITEM']['ID'], '>=PROPERTY_END_DATE' => date('Y-m-d H:i:s', strtotime('- 1 week'))];
                $last_week_schedules = \Teaching\SheduleCourses::getArray($last_week_filter);
                $schedule_ids = [];
                foreach ($last_week_schedules as $last_week_schedule){
                    $schedule_ids[] = $last_week_schedule['ID'];
                }
                if(check_full_array($schedule_ids)){
                    $completion = current(
                        (new \Teaching\CourseCompletion())->get(
                            [
                                'UF_DIDNT_COM' => false,
                                'UF_WAS_ON_COURSE' => 1,
                                'UF_SHEDULE_ID' => $schedule_ids,
                                'UF_COURSE_ID' => $arResult['ITEM']['ID'],
                                'UF_USER_ID' => $USER->GetID()
                            ]
                        )
                    );
                }
                $status = \Models\Course::getStatus($arResult['ITEM']["ID"]);
                if(!$arResult['ITEM']['IS_COMPLETE']&&(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID'])){?>
                    <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> Попытки исчерпаны</span>
                <?php } else {
                    if(!\Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID']) && !\Models\Course::allowToFreeEnroll($arResult['ITEM']['ID'])) {?>
                        <span class="status status--not-passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete.svg" alt=""></span> На балансе дилера недостаточно средств для записи</span>
                    <?php }
                    if ($status=="expired") {?>
                        <span class="status status--not-passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete.svg" alt=""></span> Закончился срок действия сертификата</span>
                    <?php } elseif($status=="expired_date") {?>
                        <span class="status status--not-passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete.svg" alt=""></span> Закончился срок действия сертификата</span>
                    <?php } elseif($status=="retest_failed") {?>
                        <span class="status status--not-passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete.svg" alt=""></span> Ретест провален</span>
                    <?php }
                    if($arResult['ITEM']['INFO']) {
                        if($status=="uncompleted") {

                        } else {?>
                            <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span> <?=$arResult['ITEM']['INFO']?></span>
                        <?php }
                    }
                }

            //dump($arResult['ITEM']);
            if($arResult['ITEM']['WAS_STARTED'] && $arResult['ITEM']['IS_HYBRID']) {

                    if (\Models\Course::hasIncomingTest($arResult['ITEM']['ID'])) {
                        if(\Models\Course::hasUncompletingIncomingTest($arResult['ITEM']['ID'])) {?>
                            <a href="<?= \Teaching\Tests::generateLinkToIncomingTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти предварительный тест</a>
                        <?php } else {
                            if($arResult['ITEM']['CURRENT_SHEDULE']['PROPERTIES']['ALLOW_MAIN_TEST']=='Да') {?>
                                <a href="<?= \Teaching\Tests::generateLinkToTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти тест</a>
                            <?php } else { ?>
                                <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span>Выходное тестирование еще не разрешено</span>
                            <?php }
                        }
                    } else {
                        if($arResult['ITEM']['CURRENT_SHEDULE']['PROPERTIES']['ALLOW_MAIN_TEST']=='Да') {?>
                            <a href="<?= \Teaching\Tests::generateLinkToTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти тест</a>
                        <?php } else { ?>
                            <span class="status status--passed status--lg"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check3.svg" alt=""></span>Выходное тестирование еще не разрешено</span>
                        <?php }
                    }?>
                <?php } ?>
                <?php if($arResult['ITEM']['IS_FOR_SINGLE_STUDY']) {
                    ?>
                    <?php if($arResult['ITEM']['WAS_STARTED']) {?>
                        <a href="<?=$arResult['ITEM']['COMPLETION_LINK']?>" class="btn btn--reverse"><?= Loc::getMessage('TO_PROCESS') ?></a>
                    <?php }
                    elseif ($status=='expired') {
                        if($arResult['ITEM']['PROPERTIES']["HAS_RETEST"] == "Да") {
                            if (\Models\Course::isScormCourse($arResult['ITEM']['ID']) && !\Models\Course::ScormCourseHasRetest($arResult['ITEM']['ID'])) {?>
                                <a href="<?= \Teaching\Tests::generateLinkToScormReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>
                            <?php } else {?>
                                <a href="<?= \Teaching\Tests::generateLinkToReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>
                            <?php }
                        } else {
                            if($arResult['ITEM']['REGISTER_BUTTON']['NOT_NEED_SHOW']) {

                            } else {
                                if(!(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID'])){?>
                                    <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                        <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                                    <?php }?>
                                <?php }
                            }
                        }
                    } elseif($status=='retest_failed') {
                        if(!(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID'])){?>
                            <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                            <?php }?>
                        <?php }
                    } elseif($arResult['ITEM']['IS_COMPLETED_COURSE']) {?>
                        <?php if( $status=='expired') {
                                if($arResult['ITEM']['PROPERTIES']["HAS_RETEST"] == "Да") {
                                    if (\Models\Course::isScormCourse($arResult['ITEM']['ID']) && !\Models\Course::ScormCourseHasRetest($arResult['ITEM']['ID'])) {?>
                                        <a href="<?= \Teaching\Tests::generateLinkToScormReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>
                                    <?php } elseif(\Models\Course::hasOnlyScormRetest($arResult['ITEM']['ID'])) {?>
                                        <a href="<?= \Teaching\Tests::generateLinkToScormReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>

                                    <?php } else {?>
                                        <a href="<?= \Teaching\Tests::generateLinkToReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>
                                    <?php }
                                } else {
                                    if($arResult['ITEM']['REGISTER_BUTTON']['NOT_NEED_SHOW']) {

                                    } else {
                                        if(!(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID'])){?>
                                            <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                                <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                                            <?php }?>
                                        <?php }
                                    }
                                }?>
                            <?php } else {

                                if( $status=='uncompleted') {
                                    if($arResult['ITEM']['REGISTER_BUTTON']['NOT_NEED_SHOW']) {

                                    } else {
                                        if(!(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID']) && \Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID'])){?>
                                            <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                                <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                                            <?php }?>
                                        <?php }
                                    }
                                } elseif ($status == 'expired_date') {?>
                                    <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                        <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                                    <?php }?>

                                <?php } else {?>
                                    <a href="<?=$arResult['ITEM']['COMPLETION_LINK']?>" class="btn btn--reverse"><?= Loc::getMessage('VIEW') ?></a>
                                <?php }
                            }?>
                    <?php } else {
                            if($arResult['ITEM']['REGISTER_BUTTON']['NOT_NEED_SHOW']) {

                            } else {
                                if(!(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID']) && \Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID'])){?>
                                    <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                                        <a href="javascript:void(0)" class="btn btn--reverse detail_enroll_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL')?></a>
                                    <?php }?>
                                <?php }
                            }?>
                    <?php }?>
                    <?php if($arResult['USER']['HAS_RIGHTS_TO_SET_COURSE']&&$arResult['ITEM']['FOR_SETTING']){?>
                        <a href="javascript:void(0)" class="btn btn--reverse set_course_to_employee_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('SET_TO_EMPLOYEE')?></a>
                    <?php }?>
                    <?php if($arResult['USER']['HAS_RIGHTS_TO_ENROLL_EMPLOYEE'] && \Teaching\Courses::isAllowToEnrollByBalance($arResult['ITEM']['ID'])){?>
                        <?php if(\Teaching\Courses::isAllowToEnrollByCountry($arResult['ITEM']['ID'])) {?>
                            <a href="javascript:void(0)" class="btn btn--reverse employee_enroll_butt" style="width: 200px" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('ENROLL_EMPLOYEE')?></a>
                        <?php }?>
                    <?php }?>

                <?php } else {

                    $status = \Models\Course::getStatus($arResult['ITEM']["ID"]);

                    if($status=='expired') {
                        if($arResult['ITEM']['PROPERTIES']["HAS_RETEST"] == "Да") {
                            if (\Models\Course::isScormCourse($arResult['ITEM']['ID'])) {?>
                            <?php } elseif(\Models\Course::hasOnlyScormRetest($arResult['ITEM']['ID'])) {?>
                                <a href="<?= \Teaching\Tests::generateLinkToScormReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>

                            <?php } else {?>
                                <a href="<?= \Teaching\Tests::generateLinkToReTest($arResult['ITEM']['ID'])?>" class="btn btn--reverse">Пройти ретест</a>
                            <?php } ?>
                        <?php }
                    } ?>
                    <?php if($arResult['ITEM']['HAS_FREE_PLACES'] && $arResult['USER']['HAS_RIGHTS_TO_SET_COURSE']&&$arResult['ITEM']['FOR_SETTING']){?>
                        <a href="javascript:void(0)" class="btn btn--reverse set_course_to_employee_butt" data-course-id="<?=$arResult['ITEM']['ID']?>"><?=Loc::getMessage('SET_TO_EMPLOYEE')?></a>
                    <?php }
                        $completion_now = current((new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $arResult['ITEM']['ID'], 'UF_USER_ID' => $USER->GetID()]));
                        if(check_full_array($completion_now)&&!\Models\FeedbackPoll::isEnded($completion_now['ID'])){
                            if ($completion_now['UF_IS_COMPLETE'] == 1 || $completion_now['UF_FAILED'] == 1) { ?>
                                <a href="/cabinet/courses/feedback_poll/new/<?= $completion_now['ID'] ?>/"
                                   class="btn btn--reverse" data-course-id="<?= $arResult['ITEM']['ID'] ?>">Форма обратной связи</a>
                            <?php }
                        }
                }
                if($_REQUEST['load_ajax']=='enrolled_data')
                    die();?>
            </div>

                <?php if(!$arResult['ITEM']['IS_COMPLETE']&&(new \Teaching\CourseCompletion())->missAttempts($arResult['ITEM']['ID'])){?>
                    <div class="course-main-info course-main-info--third">
                        <?php $teaching_admin = \Models\User::getTeachingAdminByCourseAndUser($arResult['ITEM']['ID']);
                        if(check_full_array($teaching_admin)){
                            ?><p>Курс не сдан, попытки прохождения исчерпаны. Обратитесь к <?=$teaching_admin['NAME']?> <?=$teaching_admin['LAST_NAME']?>, <?=$teaching_admin['EMAIL']?>, <?=$teaching_admin['PERSONAL_MOBILE']?> для добавления попыток</p><?php
                        } else {
                            ?><p>Курс не сдан, попытки прохождения исчерпаны. Обратитесь к администратору для добавления попыток</p><?php
                        }?>
                    </div>
                <?php } ?>

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
                        <?php foreach ($arResult['ITEM']['PROPERTIES']['SCHEDULE'] as $key => $value){?>
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
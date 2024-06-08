<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;
use Teaching\MaterialsFiles;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER, $APPLICATION;?>
<div class="content-section">
    <h1 class="h1"><?=$arResult['ITEM']['NAME']?></h1>
    <div class="content-block">
        <div class="course-main-block">
            <div class="course-main">
                <div class="course-main__image"><img src="<?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PREVIEW_PICTURE']??SITE_TEMPLATE_PATH.'/images/img-3.jpg'?>" alt=""></div>
                <div class="course-main__content">
                    <div class="course-main__information">
                        <div class="course-main__info-item">
                            <span class="course-main__info-item-title"><?=GetMessage('DATE_TITLE')?></span>
                            <span class="course-main__info-item-date"><?=$arResult['ITEM']['PROPERTY_BEGIN_DATE_VALUE']?> </span>
                        </div>
                        <?php if(!empty($arResult['ITEM']['~PROPERTY_BEGIN_DATE_VALUE'])){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=GetMessage('BEGIN_TITLE')?></span>
                                <span class="course-main__info-item-time-start"><?=date('H:i', strtotime($arResult['ITEM']['~PROPERTY_BEGIN_DATE_VALUE']))?></span>
                            </div>
                        <?php }?>
                        <?php if(!empty($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_DURING_VALUE'])){?>
                            <div class="course-main__info-item">
                                <span class="course-main__info-item-title"><?=GetMessage('DURATION_TITLE')?></span>
                                <span class="course-main__info-item-duration"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_DURING_VALUE']?></span>
                            </div>
                        <?php }?>
                        <div class="course-main__info-item">
                            <span class="course-main__info-item-title"><?=GetMessage('FORMAT_TITLE')?></span>
                            <span class="course-main__info-item-format offline"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_FORMAT_VALUE']?></span>
                        </div>

                        <div class="course-main__info-item">
                            <span class="course-main__info-item-title"><?=GetMessage('FOR_TITLE')?></span>
                            <p><?=GetMessage('FOR')?> <?=$arResult['ITEM']['FOR_ROLES']?> </p>
                        </div>
                    </div>
                    <div class="course-main__contacts">
                        <?php /*<div class="course-main__adress">
                            <div class="course-main__city">
                                <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/location-empty.svg" alt=""></span>
                                Москва
                            </div>
                            ул. Бутырский Вал, д. 68/70, стр. 1, <br>
                            Бизнес Центр «Baker Plaza»
                        </div>*/?>
                        <div class="course-main__map">
                            <iframe
                                    src="https://yandex.ru/map-widget/v1/?um=constructor%3Af6b155c37528d3d03b8a07522c664e66f998969f58fad722f1ecd0e7eb1915d1&amp;source=constructor"
                                    frameborder="0"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <div class="course-main-info course-main-info--third">
                <?php if(!empty($arResult['ITEM']['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'])&&!empty($arResult['ITEM']['PROPERTY_END_REGISTRATION_DATE_VALUE'])){?>
                <div class="course-main-info-item">
                    <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/list-icon.svg" alt=""></span>
                    <span class="course-main-info-item__title"><?= Loc::getMessage('ALLOW_REGISTER') ?><br><?= Loc::getMessage('ALLOW') ?></span>
                    <span class="course-main-info-item__numbers">
                        <?php dump($arResult['ITEM']['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'])?>
                    <?php if(str_contains("00:00", $arResult['ITEM']['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'])){?>
                        <?= DateHelper::getHumanDate($arResult['ITEM']['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'], 'd.m')?> - <?= DateHelper::getHumanDate($arResult['ITEM']['PROPERTY_END_REGISTRATION_DATE_VALUE'], 'd.m')?>
                    <?php } else {?>
                        <?= DateHelper::getHumanDate($arResult['ITEM']['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'], 'd.m H:i')?> - <?= DateHelper::getHumanDate($arResult['ITEM']['PROPERTY_END_REGISTRATION_DATE_VALUE'], 'd.m')?>
                    <?php }?>
                    </span>
                </div>
                <?php }
                $need_cost_block_show = true;
                if (\Models\Course::isOP($arResult['ITEM']['PROPERTY_COURSE_ITEM']['ID']) && \Settings\Common::get('show_cost_in_op_courses')!="Y"){
                    $need_cost_block_show = false;
                }
                if ($need_cost_block_show){?>
                    <div class="course-main-info-item">
                        <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/purse2.svg" alt=""></span>
                        <span class="course-main-info-item__title"><?=GetMessage('COST_TITLE')?></span>
                        <?php if($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE']>0){?>
                            <span class="course-main-info-item__numbers"><?=number_format($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COST_VALUE'], 0, ',', ' ')?> <small><?= Loc::getMessage('CURRENCY') ?></small> </span>
                        <?php }else{?>
                            <span class="course-main-info-item__numbers"><?=GetMessage('FREE_ACCESS')?></span>
                        <?php }?>
                    </div>
                <?php }?>
                <?php if(!$arResult['ITEM']['PROPERTY_COURSE_ITEM']['IS_FREE']){?>
                <div class="course-main-info-item">
                    <span class="course-main-info-item__icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/user3.svg" alt=""></span>
                    <span class="course-main-info-item__title"><?=GetMessage('FREE_PLACES_TITLE')?></span>
                    <span class="course-main-info-item__numbers"> <?=$arResult['ITEM']['FREE_PLACES']?> <span>/ <?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_LIMIT_VALUE']?></span>
                </span>
                </div>
                <?php }?>
                <?php if($arResult['ITEM']['REGISTRATION_COURSE']['SHOW_REGISTER_BUTTON']){
                    if($arResult['ITEM']['IS_FREE']){?>
                        <a href="javascript:void(0)" onclick="showCalendar('<?=$arResult['ITEM']['ID']?>')" class="btn btn--reverse detail_enroll_butt" data-button-id="<?=$arResult['ITEM']['ID']?>"><?=GetMessage('ENROLL')?></a>
                    <?php }else{?>
                        <a href="javascript:void(0)" onclick="showEnrollForm('<?=$arResult['ITEM']['ID']?>', '<?=$arResult['ITEM']['PROPERTY_BEGIN_DATE_VALUE']?>', '<?= DateHelper::getHumanDate($arResult['ITEM']['~PROPERTY_BEGIN_DATE_VALUE'], 'H:i')?>')" class="btn btn--reverse detail_enroll_butt" data-button-id="<?=$arResult['ITEM']['ID']?>"><?=GetMessage('ENROLL')?></a>
                    <?php }?>
                <?php }?>
            </div>

        </div>
        <div class="text-content text-content--border">
            <?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['~PROPERTY_TEXT_BLOCK_1_VALUE']['TEXT']?>
            <?php if(count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TEXT_SLIDER_VALUE'])>0){?>
            <div class="content-slider owl-carousel">
                <?php foreach ($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TEXT_SLIDER_VALUE'] as $img_id){?>
                    <div class="item"><img src="<?=CFile::GetPath($img_id)?>" alt=""></div>
                <?php }?>
            </div>
            <?php }?>
            <?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['~PROPERTY_TEXT_BLOCK_2_VALUE']['TEXT']?>

            <?php if(count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_SCHEDULE_VALUE'])>0){?>
                <div class="timetable-event">
                    <h2 class="h2"><?=GetMessage('SCHEDULE_TITLE')?></h2>
                    <div class="timetable-event__content">
                        <?php foreach ($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_SCHEDULE_VALUE'] as $key => $value){?>
                            <div class="timetable-event-item">
                                <div class="timetable-event-item__time"><?=$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_SCHEDULE_DESCRIPTION'][$key]?></div>
                                <div class="timetable-event-item__content"><?=$value['TEXT']?></div>
                            </div>
                        <?php }?>
                    </div>
                </div>
            <?php }?>
        </div>
        <?php if(count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE'])>0){?>
        <h2 class="h2"><?=GetMessage('MAIN_EVENT_SPEAKER')?></h2>
        <div class="trainers trainers--border-none">
            <div class="trainer-item">
                <div class="trainer-item__avatar"><img src="<?=CFile::GetPath($arResult['MAIN_SPEAKER']['PREVIEW_PICTURE'])?>" alt=""></div>
                <div class="trainer-item__content">
                    <span class="trainer-item__name"><?=$arResult['MAIN_SPEAKER']['NAME']?></span>
                    <p><?=$arResult['MAIN_SPEAKER']['PREVIEW_TEXT']?></p>
                </div>
            </div>
            <?php if(count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE'])>1){?>
                <h2 class="h2"><?=GetMessage('EVENT_SPEAKERS')?></h2>
                <?php foreach ($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE'] as $key=> $value){
                    if($key==0)
                        continue;?>
                    <div class="trainer-item">
                        <div class="trainer-item__avatar"><img src="<?=CFile::GetPath($arResult['SPEAKERS'][$value]['PREVIEW_PICTURE'])?>" alt=""></div>
                        <div class="trainer-item__content">
                            <span class="trainer-item__name"><?=$arResult['SPEAKERS'][$value]['NAME']?></span>
                            <p><?=$arResult['SPEAKERS'][$value]['PREVIEW_TEXT']?></p>
                        </div>
                    </div>
                <?php }?>
            <?php }?>
        </div>
        <?php }?>
    </div>
</div>
<?php if($arResult['MATERIALS_FILES']&&count($arResult['MATERIALS_FILES'])>0){?>
<div class="content-section">
    <h2 class="h2"><?=GetMessage('MATERIALS_TITLE')?></h2>
    <div class="content-block">
        <div class="materials">
            <?php foreach ($arResult['MATERIALS_FILES'] as $file){?>
                <a href="<?=$file['FILE_INFO']['SRC']?>" download="<?=$file['FILE_INFO']['ORIGINAL_NAME']?>" class="material-download-item">
                <span class="material-download-item__title"><?=$file['NAME']?></span>
                <span class="material-download-item__icon">
                    <span class="icon">
                        <img src="<?=$file['FILE_INFO']['FILE_ICON']?>" alt="">
                    </span>
                    (<?= MaterialsFiles::resizeBytes($file['FILE_INFO']['FILE_SIZE']);?> <?=GetMessage('MB')?>)
                </span>
                </a>
            <?php }?>
        </div>
    </div>
</div>
<?php }?>
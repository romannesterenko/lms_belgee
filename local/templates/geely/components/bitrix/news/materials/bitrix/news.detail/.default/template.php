<?php use Bitrix\Main\Localization\Loc;
use Helpers\UserHelper;
use Teaching\Courses;
use Teaching\MaterialsFiles;
use Teaching\SheduleCourses;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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
//dd($arResult);
?>
<div class="content-block text-block content-block--margin">
    <h2><?=$arResult['NAME']?></h2>
    <div class="text-align-center-image">
        <?if($arResult['DETAIL_PICTURE']['SRC']):?><span class="text-align-center-image__image"><img src="<?= $arResult['DETAIL_PICTURE']['SRC'] ?>" alt=""></span><?endif;?>
        <?php if (!empty($arResult['PROPERTIES']['TEXT_BLOCK_1']['~VALUE']['TEXT'])) { ?>
            <?= $arResult['PROPERTIES']['TEXT_BLOCK_1']['~VALUE']['TEXT'] ?>
        <?php } ?>
    </div>
    <?php if (!empty($arResult['PROPERTIES']['YOUTUBE_LINK']['VALUE'])) { ?>
        <div class="video-content">
            <div class="video" data-video-id="<?= $arResult['PROPERTIES']['YOUTUBE_LINK']['VALUE'] ?>">
                <div class="video-layer">
                    <div class="video-placeholder">
                        <!-- ^ div is replaced by the YouTube video -->
                    </div>
                </div>
                <div class="video-preview"
                     style="background: url(<?= SITE_TEMPLATE_PATH ?>/images/big-img-2.jpg) 50% 50% no-repeat">
                    <!-- this icon would normally be implemented as a character in an icon font or svg spritesheet, or similar -->
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/play-youtube.svg" class="video-play" alt="">
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if (!empty($arResult['PROPERTIES']['TEXT_BLOCK_2']['~VALUE']['TEXT'])) { ?>
        <?= $arResult['PROPERTIES']['TEXT_BLOCK_2']['~VALUE']['TEXT'] ?>
    <?php } ?>
    <?php if ($arResult['PROPERTIES']['COURSE_IN_TEXT']['VALUE'] > 0) {
        $schedule = SheduleCourses::getById($arResult['PROPERTIES']['COURSE_IN_TEXT']['VALUE']);
        $schedule = array_shift($schedule);
        $schedule['COURSE'] = Courses::getById($schedule['PROPERTIES']['COURSE']);
        ?>
        <div class="text-content">
            <div class="course-attention">
                <a href="/courses/<?=$schedule['COURSE']['CODE']?>/">
                  <span class="course-attention__image">
                    <img src="<?=$schedule['COURSE']['PREVIEW_PICTURE']>0?CFile::GetPath($schedule['COURSE']['PREVIEW_PICTURE']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt="">
                    <?php /*<span class="course-attention__category"> <span class="icon icon-check"></span>Обязательный курс</span>*/?>
                  </span>
                    <span class="course-attention__content">
                        <?php /*<span class="course-attention__attention">
                          <span class="icon">!</span>
                          Внимание! Для специалистов отдела продаж!
                        </span>*/?>
                        <span class="course-attention__title"><?=$schedule['COURSE']['NAME']?></span>
                        <span class="course-attention__text"><?=$schedule['COURSE']['PREVIEW_TEXT']?></span>

                            <span class="course-attention__bottom">
                                <?php if($schedule['PROPERTIES']['COST']>0){?>
                                  <span class="course-attention__cost">
                                    <span class="icon icon-purse"></span>
                                    <?=$schedule['PROPERTIES']['COST']?> <?= Loc::getMessage('CURRENCY') ?>
                                  </span>
                                <?php }
                                if($schedule['PROPERTIES']['LIMIT']>0){?>
                                    <span class="course-attention__numbers"><?= Loc::getMessage('FREE') ?><?= SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT'])?> / <span><?=$schedule['PROPERTIES']['LIMIT']?></span></span>
                                <?php }?>
                            </span>

                    </span>
                </a>
            </div>
        </div>
    <?php } ?>
    <?php if (!empty($arResult['PROPERTIES']['TEXT_BLOCK_3']['~VALUE']['TEXT'])) { ?>
        <?= $arResult['PROPERTIES']['TEXT_BLOCK_3']['~VALUE']['TEXT'] ?>
    <?php } ?>
    <?php if (is_array($arResult['PROPERTIES']['SLIDER']['VALUE'])) if (count($arResult['PROPERTIES']['SLIDER']['VALUE']) > 0) { ?>
        <div class="text-content">
            <div class="content-slider owl-carousel">
                <?php foreach ($arResult['PROPERTIES']['SLIDER']['VALUE'] as $image_id) { ?>
                    <div class="item"><img src="<?= CFile::GetPath($image_id) ?>" alt=""></div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <?php if (!empty($arResult['PROPERTIES']['TEXT_BLOCK_4']['~VALUE']['TEXT'])) { ?>
        <?= $arResult['PROPERTIES']['TEXT_BLOCK_4']['~VALUE']['TEXT'] ?>
    <?php } ?>
    <!--    <div class="btn-center">-->
    <!--        <a href="" class="btn">Читать весь материал</a>-->
    <!--        <a href="" class="btn">Скачать всю информацию</a>-->
    <!--    </div>-->
</div>
<?php if($arResult['PROPERTIES']['AUTHOR']['VALUE']>0){
    $user = UserHelper::getByID($arResult['PROPERTIES']['AUTHOR']['VALUE']);?>
<div class="content-section">
    <h2 class="h2"><?= Loc::getMessage('AUTHOR') ?></h2>
    <div class="content-block">
        <div class="author">
            <span class="author__avatar">
              <img src="<?=$user['PERSONAL_PHOTO']>0?CFile::GetPath($user['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH."/images/No-photo-m.png"?>" alt="">
              <span class="icon"><img src="<?= SITE_TEMPLATE_PATH ?>/images/education-icon-white.svg" alt=""></span>
            </span>
            <div class="author__content">
                <span class="author__name"><?=$user['LAST_NAME']?> <?=$user['NAME']?> <?=$user['SECOND_NAME']?></span>
                <p><?=$user['PERSONAL_PROFESSION']?> </p>
                <div class="author__contacts">
                    <div class="author__social">
                        <span><a href="#"><img src="<?= SITE_TEMPLATE_PATH ?>/images/vk.svg" alt=""></a></span>
                        <span><a href="#"><img src="<?= SITE_TEMPLATE_PATH ?>/images/telegram.svg" alt=""></a></span>
                    </div>
                    <div class="author__mail">
                        <a href="#">
                            <span class="icon"><img src="<?= SITE_TEMPLATE_PATH ?>/images/notice-mail.svg" alt=""></span>
                            <?=$user['EMAIL']?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }?>
<?php if(!empty($arResult['PROPERTIES']['FILES']['VALUE'])&&count($arResult['PROPERTIES']['FILES']['VALUE'])>0){
    $icons = [
        'default' => SITE_TEMPLATE_PATH . '/images/zip-icon.svg',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => SITE_TEMPLATE_PATH . '/images/exel-icon.svg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => SITE_TEMPLATE_PATH . '/images/word-icon.svg',
        'application/msword' => SITE_TEMPLATE_PATH . '/images/word-icon.svg',
        'application/msexel' => SITE_TEMPLATE_PATH . '/images/exel-icon.svg',
        'application/pdf' => SITE_TEMPLATE_PATH . '/images/pdf-color-icon.svg',
        'image/jpeg' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/gif' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/png' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/svg+xml' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'text/plain' => SITE_TEMPLATE_PATH . '/images/word-icon.svg',
        'image/webp' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
    ];?>
    <div class="content-section">
        <h2 class="h2"><?= Loc::getMessage('BEFORE_MATERIALS') ?></h2>
        <div class="content-block">
            <div class="materials">
                <?php foreach ($arResult['PROPERTIES']['FILES']['VALUE'] as $file_id){
                    $file = CFile::GetFileArray($file_id);?>
                    <a href="<?=$file['SRC']?>" download="<?=$file['ORIGINAL_NAME']?>" class="material-download-item">
                        <span class="material-download-item__title"><?=$file['ORIGINAL_NAME']?></span>
                        <span class="material-download-item__icon">
                            <span class="icon">
                                <img src="<?=$icons[$file['CONTENT_TYPE']]?>" alt="">
                            </span>
                            (<?= MaterialsFiles::resizeBytes($file['FILE_SIZE']);?> <?= Loc::getMessage('MB') ?>)
                        </span>
                    </a>
                <?php }?>
            </div>
        </div>
    </div>
<?php }?>
<?php if(!empty($arResult['PROPERTIES']['COURSES']['VALUE'])&&count($arResult['PROPERTIES']['COURSES']['VALUE'])>0){
    $course = Courses::getById($arResult['PROPERTIES']['COURSES']['VALUE']);?>
    <div class="content-section">
        <h2 class="h2"><?= Loc::getMessage('SV_COURSES') ?></h2>
        <div class="content-block">
            <div class="course-list">
                <?php foreach ($arResult['PROPERTIES']['COURSES']['VALUE'] as $course_id){
                    $course = Courses::getById($course_id)?>
                    <div class="course-list-item">
                        <span class="course-list-item__icon">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/images/education-icon.svg" alt="">
                        </span>
                        <span class="course-list-item__title">
                            <a href="/courses/<?=$course['CODE']?>/">«<?=$course['NAME']?>»</a>
                        </span>
                        <span class="course-list-item__btn">
                            <a href="/courses/<?=$course['CODE']?>/">
                                <span class="icon icon-arrow-link"></span>
                            </a>
                        </span>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
<?php }?>

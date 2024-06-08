<?php
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
//dd($arResult);
$obRating = new \Teaching\Rating()
?>
<div class="content-block">

    <div class="profile-main">
        <?php
        if ($arResult['arUser']['PERSONAL_PHOTO']):
            $pic = CFile::GetPath($arResult['arUser']['PERSONAL_PHOTO']);
        else:
            $pic = SITE_TEMPLATE_PATH . "/images/default_avatar.svg";
        endif;
        ?>
        <div class="profile-main__avatar"><a href="#"><img src="<?= $pic ?>" alt=""></a></div>
        <div class="profile-main__content">
            <span class="profile-main__name"><?= $arResult['arUser']['LAST_NAME'] ?> <?= $arResult['arUser']['NAME'] ?> <?= $arResult['arUser']['SECOND_NAME'] ?></span>
            <span class="profile-main__name-en"><?= CUtil::translit($arResult['arUser']['LAST_NAME'], 'ru', array("replace_space" => "-", "replace_other" => "-", "change_case" => false)) ?> <?= CUtil::translit($arResult['arUser']['NAME'], 'ru', array("replace_space" => "-", "replace_other" => "-", "change_case" => false)) ?> <?= CUtil::translit($arResult['arUser']['SECOND_NAME'], 'ru', array("replace_space" => "-", "replace_other" => "-", "change_case" => false)) ?></span>
            <span class="profile-main__id">ID: <?= $arResult['arUser']['ID'] ?></span>

            <span class="profile-main__id">Level: <?= ( $obRating->arLevels[$obRating->getUserLevelByUserId($arResult['arUser']['ID'])])?  $obRating->arLevels[$obRating->getUserLevelByUserId($arResult['arUser']['ID'])] : '0' ?></span>
          
            <span class="profile-main__status">
                  <span class="status status--passed"><span class="icon"><img
                                  src="<?= SITE_TEMPLATE_PATH ?>/images/check2.svg"
                                  alt=""></span><?= Loc::getMessage('IS_ACTIVE') ?></span>
                </span>
        </div>
    </div>

    <div class="profile-information">
        <div class="profile-information__section">
            <div class="profile-information__item">
                <strong><?= GetMessage('PERSONAL_BIRTHDAY') ?></strong>
                <span><?= $arResult['arUser']['PERSONAL_BIRTHDAY'] ?></span>
            </div>
            <div class="profile-information__item">
                <strong><?= GetMessage('PERSONAL_PROFESSION') ?></strong>
                <span><?= $arResult['arUser']['WORK_POSITION'] ?></span>
            </div>

            <div class="profile-information__item">
                <strong>E-mail</strong>
                <span><a href="mailto:<?= $arResult['arUser']['EMAIL'] ?>"><?= $arResult['arUser']['EMAIL'] ?></a></span>
            </div>

            <div class="profile-information__item">
                <strong><?= Loc::getMessage('PHONE') ?></strong>
                <span><a href="tel:<?= $arResult['arUser']['PERSONAL_MOBILE'] ?>"><?= $arResult['arUser']['PERSONAL_MOBILE'] ?></a></span>
            </div>

            <!--<div class="profile-information__item">
                <strong>Уровень сертификации</strong>
                <span>Специалист высокого уровня продаж</span>
            </div>-->
        </div>


        <div class="profile-information__section">
            <h3 class="h3 lowercase"><?= Loc::getMessage('DEALER_DATA') ?></h3>
            <div class="profile-information__item">
                <strong><?= Loc::getMessage('DEALER_CODE') ?></strong>
                <span><?= $arResult["arUser"]["DEALER"]["CODE"] ?></span>
            </div>

            <div class="profile-information__item">
                <strong><?= Loc::getMessage('NAME') ?></strong>
                <span><?= $arResult["arUser"]["DEALER"]["PROPERTY_ORG_NAME_VALUE"] ?> <br><?= $arResult["arUser"]["DEALER"]["PROPERTY_ENG_NAME_VALUE"] ?></span>
            </div>

            <div class="profile-information__item">
                <strong><?= Loc::getMessage('CITY') ?></strong>
                <span><?= $arResult["arUser"]["DEALER"]["PROPERTY_CITY_VALUE"] ?></span>
            </div>

            <div class="profile-information__item">
                <strong><?= Loc::getMessage('ADDRESS') ?></strong>
                <span><?= $arResult["arUser"]["DEALER"]["PROPERTY_ORG_ADDRESS_VALUE"] ?></span>
            </div>


        </div>
    </div>

</div>

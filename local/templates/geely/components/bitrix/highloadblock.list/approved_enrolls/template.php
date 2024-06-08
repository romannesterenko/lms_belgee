<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
<div class="content-block content-block--margin">
    <h1 class="h1 lowercase"><?=$arResult['SCHEDULE']['NAME']?></h1>
    <div class="event-info">
              <span class="event-info__item"><?= Loc::getMessage('APPR_ENROLLS_DATE_BEGIN') ?><span class="event-info__item-date"><?= DateHelper::getHumanDate($arResult['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd F')?></span></span>
        <span class="event-info__item"><?= Loc::getMessage('APPR_ENROLLS_FORMAT') ?><span
                    class="event-info__item-offline"><?=$arResult['COURSE']['PROPERTIES']['COURSE_FORMAT']?></span></span>
        <span class="event-info__item"><?= Loc::getMessage('APPR_ENROLLS_FOR_WHOM') ?><span><?= Loc::getMessage('APPR_ENROLLS_FOR') ?><?=implode(' / ', $arResult['ROLES']);?></span></span>
    </div>


    <div class="applications">
        <div class="applications__nav">
            <?php if(CSite::InDir('/cabinet/confirmation/new/')){?>
                <span class="active"><a href="#"><?= Loc::getMessage('APPR_ENROLLS_NEW') ?></a></span>
            <?php }else{?>
                <span><a href="/cabinet/confirmation/new/<?=$arResult['SCHEDULE']['ID']?>/"><?= Loc::getMessage('APPR_ENROLLS_NEW') ?></a></span>
            <?php }?>
            <?php if(CSite::InDir('/cabinet/confirmation/approved/')){?>
                <span class="active"><a href="#"><?= Loc::getMessage('APPROVED') ?></a></span>
            <?php }else{?>
                <span><a href="/cabinet/confirmation/approved/<?=$arResult['SCHEDULE']['ID']?>/"><?= Loc::getMessage('APPROVED') ?><</a></span>
            <?php }?>
        </div>
        <div class="applications__content">
            <?php if(count($arResult['rows'])==0){?>
                <p><?= Loc::getMessage('NO_ENROLLS_NOW') ?></p>
            <?php }else{
                foreach ($arResult['rows'] as $row){?>
                    <div class="application-item">
                        <?php /*<a href="#" class="delete course_application_actions" data-action="reject" data-id="<?=$row['ID']?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/delete-icon.svg" alt=""></a>*/?>
                        <div class="application-item__avatar"><img src="<?=$row['USER']['PERSONAL_PHOTO']>0?CFile::GetPath($row['USER']['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt=""></div>
                        <div class="application-item__content">
                            <span class="application-item__name"><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></span>
                            <span class="application-item__position"><?=$row['USER']['PERSONAL_PROFESSION']?></span>
                            <span class="application-item__date"><?=$row['UF_DATE']?></span>
                        </div>
                        <?php if(!$arResult['SCHEDULE']['ENDED']&&$arResult['SCHEDULE']['ALLOW_CANCEL']){?>
                            <div class="application-item__btns">
                                <a href="#" class="btn btn--small course_application_actions" data-action="reject" data-id="<?=$row['ID']?>">
                                    <svg class="icon" width="9px" height="4px">
                                        <use xlink:href="#minus-btn"></use>
                                    </svg>
                                </a>
                                <a href="#" class="btn btn--small replace_employee_popup" data-action="replace" data-id="<?=$row['ID']?>">
                                    <b>&#8645;</b>
                                </a>
                            </div>
                        <?php }?>
                        <?php /*if($arResult['SCHEDULE']['ENDED']){*/?><!--
                            <div class="application-item__btns">
                                <a href="#" class="btn btn--small" title="Не явился на курс" data-id="<?/*=$row['ID']*/?>">
                                    <b>&#8655;</b>
                                </a>
                            </div>
                        --><?php /*}*/?>
                    </div>
                <?php
                }
            }?>
        </div>
    </div>


</div>
<?php
if ($arParams['ROWS_PER_PAGE'] > 0) {
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "navigation", Array(
        "NAV_OBJECT" => $arResult["nav_object"],
        "SEF_MODE" => "N"
    ),
        false
    );
}?>
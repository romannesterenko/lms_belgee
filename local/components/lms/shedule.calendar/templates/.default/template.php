<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
global $USER;
?>
<h2 class="h2"><?=GetMessage("BLOCK_TITLE")?></h2>
<div class="content-block  content-block--margin">
    <div class="calendar">
        <div class="calendar__top">
            <a class="calendar__prev">
                <span class="icon-arrow-link" onclick="location.href='<?=$arResult['LINKS']['PREV_MONTH_LINK']?>'"></span>
            </a>
            <span class="calendar__title"><?=$arResult['MONTHS'][(int)$arParams['MONTH']]?></span>
            <a class="calendar__next">
                <span class="icon-arrow-link" onclick="location.href='<?=$arResult['LINKS']['NEXT_MONTH_LINK']?>'"></span>
            </a>
        </div>
        <div class="calendar__head">
            <div class="calendar__week-title">№</div>
            <div class="calendar__day-title"><?= Loc::getMessage('MONDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('TUESDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('WEDNESDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('THURSDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('FRIDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('SATURDAY') ?></div>
            <div class="calendar__day-title"><?= Loc::getMessage('SUNDAY') ?></div>
        </div>
        <?php foreach ($arResult['DAYS'] as $week_number => $days){?>
            <div class="calendar__line">
                <div class="calendar__week-number"><?=$week_number?></div>
                <?php foreach ($days as $day){

                    if($day['disabled']){?>
                        <div class="calendar__day-item disabled">
                            <span class="calendar__day-number "><?=$day['day_num']?></span>
                        </div>
                    <?php } else {
                        $day['date'] = date('j.m.Y', strtotime($day['date']));
                        if(!empty($arResult['ENROLLS'][$day['date']])){?>
                            <span class="calendar__day-item active">
                                <span class="calendar__day-number"><?=$day['day_num']?></span>
                                <?php foreach ($arResult['ENROLLS'][$day['date']] as $event){?>
                                    <a target="_blank" href="/shedules/<?=$event['ID']?>/" class="calendar__event">
                                        <span class="calendar__text">«<?=$event['NAME']?>» </span>
                                        <span class="calendar__date"><?=$day['date_format']?></span>
                                    </a>
                                <?php }?>
                            </span>
                        <?php }else{?>
                            <div class="calendar__day-item">
                                <span class="calendar__day-number"><?=$day['day_num']?></span>
                            </div>
                        <?php }?>
                    <?php }?>
                <?php }?>
            </div>
        <?php }?>
    </div>
</div>


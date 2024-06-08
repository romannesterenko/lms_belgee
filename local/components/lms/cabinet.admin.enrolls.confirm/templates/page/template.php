<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>

<div class="table-block">
    <?php if(is_array($arResult['ITEMS'])&&count($arResult['ITEMS'])>0){?>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
        <thead class="thead-dark">
        <tr>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_COURSE')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_DATE')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_STATUS')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_TYPE')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_ENROLLED')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_ENROLLED_FROM_DC')?></th>
            <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_NEW')?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['ITEMS'] as $ITEM) {?>
            <tr>
                <td>
                    <a href="<?=$ITEM['DETAIL_PAGE_URL']?>">
                        <?=$ITEM['NAME']?>
                        <span><?= Loc::getMessage('FOR') ?> <?=$ITEM['FOR_ROLES']?> </span>
                    </a>
                </td>
                <td><?= DateHelper::getHumanDate($ITEM['PROPERTIES']['BEGIN_DATE'], 'd.m.Y')?> - <?= DateHelper::getHumanDate($ITEM['PROPERTIES']['END_DATE'], 'd.m.Y')?></td>
                <td><?=$ITEM['ENDED']?"Да":"Нет"?></td>
                <td><?=$ITEM['COURSE_FORMAT']['VALUE_ENUM']??'Offline'?></td>
                <td><?=count($ITEM['ENROLLS']['APPROVED'])?></td>
                <td><?=$ITEM['ENROLLS']['APPROVED_DC']?></td>
                <td><?=count($ITEM['ENROLLS']['NOT_APPROVED'])?></td>
                <?php /*<td>18 из 40</td>*/?>
                <td>
                    <div class="btn-center">
                        <a href="<?=$ITEM['DETAIL_PAGE_URL']?>" class="btn" style="padding: 0px 10px">Подробнее</a>
                    </div>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <?php }else{?>
        <p><?= Loc::getMessage('NO_ROWS') ?></p>
    <?php }?>
</div>



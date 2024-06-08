<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>

<h3 class="h3 center"><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TITLE')?></h3>

<div class="table-block">
    <?php if(check_full_array($arResult['ITEMS'])){?>
        <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
            <thead class="thead-dark">
            <tr>
                <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_COURSE')?></th>
                <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_DATE')?></th>
                <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_TYPE')?></th>
                <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_ENROLLED')?></th>
                <th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_NEW')?></th>
                <?php /*<th><?=GetMessage('ADMIN_ENROLLS_CONFIRM_TABLE_TH_STEP')?></th>*/?>
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
                    <td><?= DateHelper::getHumanDate($ITEM['PROPERTIES']['BEGIN_DATE'], 'd.m')?> - <?= DateHelper::getHumanDate($ITEM['PROPERTIES']['END_DATE'], 'd.m')?></td>
                    <td><?=$ITEM['COURSE_FORMAT']['VALUE_ENUM']??'Offline'?></td>
                    <td><?=count($ITEM['ENROLLS']['APPROVED'])?></td>
                    <td><?=count($ITEM['ENROLLS']['NOT_APPROVED'])?></td>
                    <?php /*<td>18 из 40</td>*/?>
                    <td>
                        <a href="<?=$ITEM['DETAIL_PAGE_URL']?>">
                            <svg class="icon" width="7px" height="12px">
                                <use xlink:href="#table-arrow"></use>
                            </svg>
                        </a>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <div class="content-show-link">
            <a href="/cabinet/teaching/approving/">
                <?=GetMessage('ADMIN_ENROLLS_CONFIRM_SHOW_ALL')?>
                <span class="icon icon-arrow-link"></span>
            </a>
        </div>
    <?php }else{?>
        <p><?= Loc::getMessage('NO_ROWS') ?></p>
    <?php }?>
</div>



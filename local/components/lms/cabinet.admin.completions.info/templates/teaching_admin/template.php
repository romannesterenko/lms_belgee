<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;
use Teaching\SheduleCourses;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<h3 class="h3 center"><?=GetMessage('ADMIN_COMPLETIONS_INFO_TITLE')?></h3>
<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
        <thead class="thead-dark">
        <tr>
            <th><?=GetMessage('ADMIN_COMPLETIONS_INFO_TABLE_TH_COURSE')?></th>
            <th><?=GetMessage('ADMIN_COMPLETIONS_INFO_TABLE_TH_DATE')?></th>
            <?php /*<th><?=GetMessage('ADMIN_COMPLETIONS_INFO_TABLE_TH_STEP')?></th>*/?>
            <th><?=GetMessage('ADMIN_COMPLETIONS_INFO_TABLE_TH_ENROLLED')?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['ITEMS'] as $ITEM) {?>
            <tr>
                <td>
                    <div class="user-list">
                        <a href="<?=$ITEM['DETAIL_PAGE_URL']?>">
                            <?=$ITEM['NAME']?>
                            <span><?= Loc::getMessage('FOR') ?> <?=$ITEM['FOR_ROLES']?> </span>
                        </a>
                        <?php if(count($ITEM['USERS'])>0){?>
                            <div class="user-list__content">
                                <ul>
                                    <?php foreach ($ITEM['USERS'] as $user){?>
                                        <li><a href=""><?=$user['LAST_NAME']?> <?=$user['NAME']?></a></li>
                                    <?php }?>
                                </ul>
                                <?php /*?><div class="btn-center">
                                    <a href="#" class="btn">Показать всех</a>
                                </div><?*/?>
                            </div>
                        <?php }?>
                    </div>
                </td>
                <td><?= DateHelper::getHumanDate($ITEM['PROPERTIES']['BEGIN_DATE'], 'd.m')?> - <?= DateHelper::getHumanDate($ITEM['PROPERTIES']['END_DATE'], 'd.m')?></td>
                <?php /*<td>18 из 40</td>*/?>
                <td><?= SheduleCourses::getExistsPlaces($ITEM['ID'])?> / <?=$ITEM['PROPERTIES']['LIMIT']?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <div class="content-show-link">
        <a href="/cabinet/teaching/training_attendance/">
            <?=GetMessage('ADMIN_COMPLETIONS_INFO_SHOW_ALL')?>
            <span class="icon icon-arrow-link"></span>
        </a>
    </div>
</div>



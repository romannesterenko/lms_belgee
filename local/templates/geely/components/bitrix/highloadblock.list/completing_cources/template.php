<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
<div class="table-block">
    <?php if(count($arResult['rows'])>0){?>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-3">
        <thead class="thead-dark">
            <tr>
                <th><?=GetMessage('COMPLETING_COURSES_COURSE')?></th>
                <th><?=GetMessage('COMPLETING_COURSES_TYPE')?>/<?=GetMessage('COMPLETING_COURSES_ATTEMPTS')?></th>
                <th><?=GetMessage('COMPLETING_COURSES_DATE')?></th>
                <?php /*<th><?=GetMessage('COMPLETING_COURSES_POINTS')?>/<?=GetMessage('COMPLETING_COURSES_FOR_COMPLETE')?></th>*/?>

            </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['rows'] as $row){?>
            <tr>
                <td>
                    <a href="<?=$row['COURSE_LINK']?>"><?=$row['COURSE_NAME']?></a>
                </td>
                <td><?=$row['COURSE_TYPE']?>/<?= max($row['UF_MADE_ATTEMPTS'], 0) ?></td>
                <td><?=$row['UF_DATE']?></td>
                <?php /*<td><?= max($row['UF_POINTS'], 0) ?>/<?= max($row['UF_MIN_FOR_COMPLETE'], 0) ?></td>*/?>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <?php }else{?>
        <p><?= Loc::getMessage('NO_COURSES') ?></p>
    <?php }?>
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
<?php use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
        <thead class="thead-dark">
            <tr>
                <th class="text-left">ФИО</th>
                <th class="text-left">Пройден</th>
                <th class="text-left">Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($arResult['rows'] as $row){?>
                <tr>
                    <td class="text-left"><?=\Helpers\UserHelper::getFullName($row['UF_USER_ID'])?></td>
                    <td class="text-left"><?=$row['UF_IS_COMPLETE']?></td>
                    <td class="text-left"><?=$row['UF_DATE']??$row['UF_DATE_CREATE']?></td>
                </tr>
            <?php }?>
        </tbody>
    </table>
<?php
if ($arParams['ROWS_PER_PAGE'] > 0) {
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "navigation", Array(
        "NAV_OBJECT" => $arResult["nav_object"],
        "SEF_MODE" => "N"
    ),
        false
    );
}?>
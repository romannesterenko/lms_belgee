<?php

use Helpers\HLBlockHelper as HLBlock;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$_REQUEST['report_id'] = 9999999;
$items = HLBlock::get(HLBlock::initialize('g_test_drive_compare_dynamic'), [
    ">=UF_CREATED_AT" => '15.08.2024 00:00:00',
    "<=UF_CREATED_AT" => '22.08.2024 23:59:59',
]);
$schedules = [];
$users = [];
$models = [];
$criterials = [];
$props = [];
$new_array = [];
foreach ($items as $item) {
    if($item['UF_SCHEDULE_ID'] && !in_array($item['UF_SCHEDULE_ID'], $schedules))
        $schedules[] = $item['UF_SCHEDULE_ID'];
    if(!in_array($item['UF_MODEL_ID'], $models))
        $models[] = $item['UF_MODEL_ID'];
    if(!in_array($item['UF_USER_ID'], $users))
        $users[] = $item['UF_USER_ID'];
    if(!in_array($item['UF_PROP_CODE'], $criterials))
        $criterials[] = $item['UF_PROP_CODE'];
    if(!in_array($item['UF_PROP_CODE'], $props))
        $props[] = $item['UF_PROP_CODE'];
    //$new_array[$item['UF_SCHEDULE_ID']][$item['UF_USER_ID']][$item['UF_PROP_CODE']][] = $item;
}
\Helpers\IBlockHelper::includeIBlockModule();

//список расписаний
$res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], [
    'IBLOCK_ID' => 26,
    'ID' => $schedules
]);
while ($arItem = $res->fetch()) {
    $schedules_array[$arItem['ID']] = $arItem;
}
foreach ($users as $user) {
    $user_arr = \Models\User::find($user, ['ID', 'NAME', 'LAST_NAME']);
    $users_array[$user_arr['ID']] = $user_arr;
}

//список моделей
$resModels = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], [
    'IBLOCK_ID' => 25,
    'ID' => $models
]);
while ($arItemModels = $resModels->fetch()) {
    $models_array[$arItemModels['ID']] = $arItemModels;
}
foreach ($users as $user) {
    $user_arr = \Models\User::find($user, ['ID', 'NAME', 'LAST_NAME']);
    $users_array[$user_arr['ID']] = $user_arr;
}

//список критериев
$resCriterials = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], [
    'IBLOCK_ID' => 29,
    'ID' => $criterials
]);
while ($arItemCriterials = $resCriterials->fetch()) {
    $criterials_array[$arItemCriterials['ID']] = $arItemCriterials;
}
foreach ($users as $user) {
    $user_arr = \Models\User::find($user, ['ID', 'NAME', 'LAST_NAME']);
    $users_array[$user_arr['ID']] = $user_arr;
}
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">

                    <div class="btn-center">
                        <button class="btn" id="gen"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID сотрудника</th>
                            <th>Сотрудник</th>
                            <th>Критерий</th>
                            <th>Модель</th>
                            <th>Оценка</th>
                            <th>Дата</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item){?>
                            <tr>
                                <td><?=$item['UF_USER_ID']?></td>
                                <td><?=$users_array[$item['UF_USER_ID']]['NAME']?> <?=$users_array[$item['UF_USER_ID']]['LAST_NAME']?></td>
                                <td><?=$criterials_array[$item['UF_PROP_CODE']]['NAME']?></td>
                                <td><?=$models_array[$item['UF_MODEL_ID']]['NAME']?></td>
                                <td><?=$item['UF_USER_RATING']?></td>
                                <td><?=$item['UF_CREATED_AT']->format('d.m.Y')?></td>
                            </tr>

                        <?php }?>
                        <?php /*foreach ($new_array as $shedule_id => $new_array_item) {*/?><!--
                            <tr>
                                <td colspan="3" class="text-center">
                                    <b><?php /*=$schedules_array[$shedule_id]['NAME']*/?></b>
                                </td>
                            </tr>
                            <?php /*foreach ($new_array_item as $uid => $new_array_sub_item){*/?>
                                <?php
/*                                $first = true;
                                foreach ($new_array_sub_item as $qid => $last_item) {*/?>
                                    <tr>
                                        <?php /*if($first) {*/?>
                                            <td rowspan="<?php /*=count($new_array_sub_item)*/?>"><?php /*=$users_array[$uid]['NAME']*/?> <?php /*=$users_array[$uid]['LAST_NAME']*/?> (ID: <?php /*=$uid*/?>)</td>
                                        <?php /*}*/?>
                                        <td class="text-left" ><?php /*=trim($criterials_array[$qid]['NAME'])*/?></td>
                                        <td class="text-left" >
                                            <?php /*foreach ($last_item as $grade_array){
                                                echo $models_array[$grade_array['UF_MODEL_ID']]['NAME'].": ".$grade_array['UF_USER_RATING']."<br>";
                                            }*/?>
                                        </td>
                                    </tr>
                                <?php
/*                                $first = false;
                                }*/?>

                            <?php /*}*/?>
                        --><?php /*}*/?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

<?php
dump($users_array);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
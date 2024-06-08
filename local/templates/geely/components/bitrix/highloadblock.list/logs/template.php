<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */

?>
<div class="content-block content-block--margin">
    <h1 class="h1 lowercase"><?=$arResult['SCHEDULE']['NAME']?></h1>
    <table class="table table-bordered table-striped table-responsive-stack table--white" id="table-1">
        <thead  class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Сущность</th>
                <th>Id cущности</th>
                <th>Действие</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($arResult['rows'] as $row) {
                $author = \Models\User::getById($row['UF_USER_ID']);
                ?>
                <tr>
                    <td class="text-center"><a href="?action=detail&ID=<?=$row['ID']?>"><?=$row['ID']?></a></td>
                    <?php if($row['UF_USER_ID']==0){?>
                        <td>-</td>
                    <?php } else {?>
                        <td><?=$author['NAME']?> <?=$author['LAST_NAME']?> (ID: <?=$author['ID']?>)</td>
                    <?php }?>
                    <td><?=$row['UF_ENTITY']?></td>
                    <?php if($row['UF_ENTITY']=='Пользователь'){
                        $user = \Models\User::getById((int)$row['UF_ENTITY_ID']);?>
                        <td><?=$user['NAME']?> <?=$user['LAST_NAME']?> (ID: <?=$user['ID']?>)</td>
                    <?php } elseif($row['UF_ENTITY']=='Прохождение курса') {
                        $completion = current((new \Teaching\CourseCompletion())->get(['ID' => $row['UF_ENTITY_ID']]));
                        if(check_full_array($completion)){
                            $course = \Models\Course::find($completion['UF_COURSE_ID'], ['NAME']);?>
                            <td><?=$course['NAME']?> <b>(<?=$completion['UF_DATE']?>)</b></td>
                        <?php }
                    } else {?>
                        <td><?=$row['UF_ENTITY_ID']?></td>
                    <?php }?>
                    <td><?=$row['UF_ACTION']?></td>
                    <td><?=$row['UF_CREATED_AT']?></td>
                </tr>
            <?php }?>
        </tbody>

    </table>
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
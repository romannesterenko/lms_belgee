<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$schedule = current(\Teaching\SheduleCourses::getArray(['ID' => $request['shedule_id']]));
$completions = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $request['shedule_id']])
?>
<div class="table-block">
    <h4 class="text-center">Список участников на тренинг <?=$schedule['NAME']?><br/><br/><?=$schedule['PROPERTY_BEGIN_DATE_VALUE']?> - <?=$schedule['PROPERTY_END_DATE_VALUE']?><br/><br/></h4>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-2">
        <thead class="thead-dark">
            <tr>
                <th></th>
                <th class="text-left">Сотрудник</th>
                <th class="text-left">Дилер</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($completions as $key => $completion){
            $user = \Models\User::find($completion['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER']);
            $dealer = \Models\Dealer::find($user['UF_DEALER']);
            ?>
            <tr class="participant_row" data-completion="<?=$completion['ID']?>">
                <td><?=($key+1)?></td>
                <td class="text-left"><?=$user['LAST_NAME']?> <?=$user['NAME']?> </td>
                <td class="text-left"><?=$dealer['NAME']?></td>
                <td><button class="btn delete_participant_completion" data-completion="<?=$completion['ID']?>">Удалить</button></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>


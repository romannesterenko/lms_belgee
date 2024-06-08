<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$dealer = \Models\Dealer::find($request['dealer_id']);
$users = \Models\Employee::getListByDealer($request['dealer_id']);
?>
<div class="table-block">
    <div class="text-center">
        <h4 class="text-center">Список сотрудников в дилере <?=$dealer['NAME']?></h4><br />
        <button class="btn deactivate_dealer" style="margin-bottom: 15px" data-dealer="<?=$dealer['ID']?>">Деактивировать дилера</button><br />
    </div>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-2">
        <thead class="thead-dark">
            <tr>
                <th></th>
                <th class="text-left">Сотрудник</th>
                <th class="text-left">Email</th>
                <th class="text-left">Телефон</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $key => $user){?>
            <tr class="participant_row" data-completion="<?=$user['ID']?>">
                <td><?=($key+1)?></td>
                <td class="text-left"><?=$user['LAST_NAME']?> <?=$user['NAME']?> </td>
                <td class="text-left"><?=$user['EMAIL']?></td>
                <td class="text-left"><?=$user['PERSONAL_MOBILE']?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>


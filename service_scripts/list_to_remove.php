<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
?>

    <h2 style="margin-top: 40px">Удаление пользователей из Telegram</h2>
    <div class="table-block">
        <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
            <thead class="thead-dark">
                <tr>
                    <th class="text-left">Имя в группе (Логин)</th>
                    <th class="text-left">ФИО в LMS</th>
                    <th class="text-left">Роль</th>
                    <th class="text-left">Канал</th>
                    <th class="text-left">Причина удаления</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (\Helpers\Tasks::getUncompletedRemovedTasks() as $uncompletedRemovedTask) {
                    $delete_reason = 'Не подходящая роль';
                    $tgUser = (new \Telegram\ChatTgUsers())->getUserByID($uncompletedRemovedTask['UF_USER_ID']);
                    $user = \Models\User::getByTgLogin($tgUser['UF_USER_LOGIN']);
                    $roles = '';
                    if(check_full_array($user)&&check_full_array($user['UF_ROLE'])){
                        $role_array = \Models\Role::getArray(['ID' => $user['UF_ROLE']]);
                        $roles = implode('<br/>', $role_array);
                    }
                    if(!check_full_array($user))
                        $delete_reason = 'Логин не внесен в систему';
                    if (empty($tgUser['UF_USER_LOGIN']))
                        $delete_reason = 'Нет логина в Telegram';
                    $chat = (new \Telegram\ChatLinks())->getByChatId(str_replace('-100', false, $uncompletedRemovedTask['UF_CHAT']));
                    ?>
                    <tr>
                        <td class="left"><?=$tgUser['UF_USER_NAME']?> (<?=$tgUser['UF_USER_LOGIN']?>)</td>
                        <td class="left"><?=check_full_array($user)?$user['NAME']." ".$user['LAST_NAME']:''?></td>
                        <td class="left"><?=$roles?></td>
                        <td class="left"><?=$chat['UF_NAME']?><br/><?=$chat['UF_LINK']?></td>
                        <td class="left"><?=$delete_reason?></td>
                    </tr>
                <?php }?>


            </tbody>
        </table>
    </div>

<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline/session.madeline_production';
$roles = \Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']);
$groups_roles = [];
foreach ($roles as $r){
    if(count($r['PROPERTY_TG_CHANEL_VALUE'])>0) {
        foreach ($r['PROPERTY_TG_CHANEL_VALUE'] as $link__){
            $groups_roles[$link__][] = $r['ID'];
        }
    }
}
$channel_users = [];
$MadelineProto = new \danog\MadelineProto\API($session_file);
$errors = [
    'INVITE_HASH_EXPIRED (400)' => 'Ссылка устарела',
    'You have not joined this chat (0)' => 'Бот не добавлен в чат',
];
?>
<div class="content-block">
    <div class="text-content text-content--long">
        <h2 class="h2 center lowercase">Привязки ролей и групп в Telegram</h2>
        <div class="table-block">
            <table class="table--borders" id="table-1" style="padding-top: 25px">
                <thead class="thead-dark">
                    <tr>
                        <th>Роль</th>
                        <th>Ссылки</th>
                        <th>Название чата</th>
                        <th>Статус подключения к TG</th>
                        <th>Всего пользователей в роли</th>
                        <th>Пользователи в роли без Telegram</th>
                        <th>Всего в группе Telegram</th>
                        <th>Пользователи без ников в Telegram</th>
                        <th>Будут добавлены в группу</th>
                        <th>Будут удалены из группы</th>
                    </tr>
                </thead>
                <tbody>
                <?php

                    foreach ($roles as $role){
                        if(count($role['PROPERTY_TG_CHANEL_VALUE'])==0) {?>
                            <tr>
                                <td class="text-left"><?=$role['NAME']?></td>
                                <td class="text-center">Не привязан Telegram</td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                                <td class="text-center"> - </td>
                            </tr>
                            <?php continue;

                        } else {
                            foreach ($role['PROPERTY_TG_CHANEL_VALUE'] as $key => $link) {
                                $users = \Models\User::getListByRole($role['ID']);
                                $users_without_tg = [];
                                $users_with_tg = [];
                                foreach ($users as $user){
                                    if(empty($user['UF_TELEGRAM'])||str_contains($user['UF_TELEGRAM'], '+7')||preg_match('/[\p{Cyrillic}]/u', $user['UF_TELEGRAM'])||str_contains($user['UF_TELEGRAM'], ' ')||is_numeric($user['UF_TELEGRAM'])){
                                        if(!str_contains($user['UF_TELEGRAM'], '@'))
                                            $user['UF_TELEGRAM'] = '@'.$user['UF_TELEGRAM'];
                                        $users_without_tg[] = ['ID' => $user['ID'], 'NAME' => $user['NAME'], 'LAST_NAME' => $user['LAST_NAME'], 'UF_TELEGRAM' => $user['UF_TELEGRAM']];
                                    } else {
                                        if(str_contains($user['UF_TELEGRAM'], 'https://t.me/')) {
                                            $array__ = explode('https://t.me/', $user['UF_TELEGRAM']);
                                            $user['UF_TELEGRAM'] = '@' . $array__[1];
                                        }
                                        if(!str_contains($user['UF_TELEGRAM'], '@'))
                                            $user['UF_TELEGRAM'] = '@'.$user['UF_TELEGRAM'];
                                        $users_with_tg[] = ['ID' => $user['ID'], 'NAME' => $user['NAME'], 'LAST_NAME' => $user['LAST_NAME'], 'UF_TELEGRAM' => $user['UF_TELEGRAM']];
                                    }
                                }
                                $status = 'Подключено';
                                $not_connected = false;
                                $tg_links = new \Telegram\ChatLinks();
                                $exist_link = $tg_links->getByLink($link);
                                $will_add_users = [];
                                $will_delete_users = [];
                                $tg_users = [];
                                $tg_users_without_login = [];
                                if(check_full_array($exist_link)){
                                    if(!empty($exist_link['UF_COMENT'])) {
                                        $not_connected = true;
                                        $status = $errors[$exist_link['UF_COMENT']] ?? $exist_link['UF_COMENT'];
                                    }
                                    $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat($exist_link['UF_CHAT_ID']);
                                    if(check_full_array($users_with_tg)&&check_full_array($tg_users)) {
                                        $user_logins = [];
                                        foreach ($users_with_tg as $u)
                                            $user_logins[$u['UF_TELEGRAM']] = $u;
                                        $tg_logins_array = [];
                                        foreach($tg_users as $one_tg_user)
                                            $tg_logins_array[] = '@'.$one_tg_user['UF_USER_LOGIN'];
                                        foreach ($users_with_tg as $one_user) {
                                            if(!in_array($one_user['UF_TELEGRAM'], $tg_logins_array))
                                                $will_add_users[] = $one_user;
                                        }
                                        foreach ($tg_users as $one_user_tg) {
                                            if(!empty($one_user_tg['UF_USER_LOGIN'])){
                                                $key_ = '@'.$one_user_tg['UF_USER_LOGIN'];
                                                if(!in_array($key_, array_keys($user_logins))){
                                                    $will_delete_users[] = $one_user_tg;
                                                }
                                            }
                                        }
                                    }

                                    foreach ($tg_users as $tg_user){
                                        if(empty($tg_user['UF_USER_LOGIN']))
                                            $tg_users_without_login[] = $tg_users;
                                    }
                                }
                                ?>
                                <tr>
                                    <?php if($key==0){?>
                                        <td class="text-left items-center" rowspan="<?=count($role['PROPERTY_TG_CHANEL_VALUE'])?>"><?=$role['NAME']?></td>
                                    <?php }?>
                                    <td><?=$link?></td>
                                    <td><?=$exist_link['UF_NAME']??''?></td>
                                    <td class="text-center"><?=$status?></td>
                                    <?php if($key==0){?>
                                        <td class="text-center items-center" rowspan="<?=count($role['PROPERTY_TG_CHANEL_VALUE'])?>">
                                            <?=count($users)?>
                                        </td>
                                        <td class="text-center items-center" rowspan="<?=count($role['PROPERTY_TG_CHANEL_VALUE'])?>">
                                            <?=count($users_without_tg)?>
                                        </td>
                                    <?php }?>
                                    <td class="text-center"><?=$not_connected?'-':'<a href="users.php?type=all_tg_users&chat_id='.$exist_link['UF_CHAT_ID'].'">'.count($tg_users).'</a>'?></td>
                                    <td class="text-center"><?=$not_connected?'-':'<a href="users.php?type=tg_users_without_logins&chat_id='.$exist_link['UF_CHAT_ID'].'">'.count($tg_users_without_login).'</a>'?></td>
                                    <td class="text-center"><?=$not_connected?'-':'<a href="users.php?type=will_add_to_group&chat_id='.$exist_link['UF_CHAT_ID'].'&role_id='.$role['ID'].'">'.count($will_add_users).'</a>'?></td>
                                    <td class="text-center"><?=$not_connected?'-':'<a href="users.php?type=will_delete_from_group&chat_id='.$exist_link['UF_CHAT_ID'].'&role_id='.$role['ID'].'">'.count($will_delete_users).'</a>'?></td>

                                </tr>
                            <?}/*
                            $tg_links = new \Telegram\ChatLinks();
                            foreach ($role['PROPERTY_TG_CHANEL_VALUE'] as $key => $link) {
                                $users_without_tg = [];
                                $users_with_tg = [];
                                foreach ($users as $user){
                                    if(empty($user['UF_TELEGRAM'])||!str_contains($user['UF_TELEGRAM'], '@')||is_numeric($user['UF_TELEGRAM'])){
                                        $users_without_tg[$link][] = ['ID' => $user['ID'], 'NAME' => $user['NAME'], 'UF_TELEGRAM' => $user['UF_TELEGRAM']];
                                    } else {
                                        $users_with_tg[$link][] = ['ID' => $user['ID'], 'NAME' => $user['NAME'], 'UF_TELEGRAM' => $user['UF_TELEGRAM']];
                                    }
                                }
                                $tg_users = [];
                                $status = 'Есть доступ';
                                $exist_link = $tg_links->getByLink($link);
                                $channel_users[$link] = $link;
                                if(!check_full_array($exist_link)) {
                                    try {
                                        $chanel = $MadelineProto->getFullInfo($link);

                                        $status = 'Есть доступ';
                                        $tg_users = [];
                                        if (check_full_array($chanel)) {
                                            (new \Telegram\ChatLinks())->addLink($chanel['chat_id'] ?? $chanel['channel_id'], $link, $chanel['type']);
                                        }
                                    } catch (Exception $e) {
                                        $status = $e->getMessage() . ' (' . $e->getCode() . ')';
                                    }
                                } else {
                                    switch ($exist_link['UF_TYPE']){
                                        case 'chat':
                                            $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat($exist_link['UF_CHAT_ID']);

                                            if(!check_full_array($tg_users)) {
                                                $tg_users = $MadelineProto->messages->getFullChat(['chat_id' => $exist_link['UF_CHAT_ID']]);
                                                if(check_full_array($tg_users)){
                                                    if(check_full_array($tg_users['users'])){
                                                        $system_u = new Telegram\ChatTgUsers();
                                                        foreach ($tg_users['users'] as $tg_user) {
                                                            if(!$system_u->isExistsUser($tg_user['id'])) {
                                                                $fields = [
                                                                    'UF_USER_NAME' => $tg_user['first_name'],
                                                                    'UF_USER_LOGIN' => $tg_user['username'],
                                                                    'UF_CHAT_ID' => $exist_link['UF_CHAT_ID'],
                                                                    'UF_USER_ID' => $tg_user['id'],
                                                                ];
                                                                $system_u->addUser($fields);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            break;
                                        case 'supergroup':
                                            $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat($exist_link['UF_CHAT_ID']);
                                            if(!check_full_array($tg_users)) {
                                                $tg_users = $MadelineProto->getPwrChat('-100' . $exist_link['UF_CHAT_ID']);
                                                if(check_full_array($tg_users)){
                                                    if(check_full_array($tg_users['participants'])){
                                                        $system_u = new Telegram\ChatTgUsers();
                                                        foreach ($tg_users['participants'] as $tg_user) {
                                                            if($tg_user['role']!='user')
                                                                continue;
                                                            if(!$system_u->isExistsUser($tg_user['user']['id'])) {
                                                                $fields = [
                                                                    'UF_USER_NAME' => $tg_user['user']['first_name'],
                                                                    'UF_USER_LOGIN' => $tg_user['user']['username']??'',
                                                                    'UF_CHAT_ID' => $exist_link['UF_CHAT_ID'],
                                                                    'UF_USER_ID' => $tg_user['user']['id'],
                                                                ];
                                                                $system_u->addUser($fields);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            break;
                                        case 'channel':
                                            $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat($exist_link['UF_CHAT_ID']);
                                            if(!check_full_array($tg_users)) {
                                                try {
                                                    $tg_users = $MadelineProto->getPwrChat('-100' . $exist_link['UF_CHAT_ID']);
                                                    if(check_full_array($tg_users)){
                                                        if(check_full_array($tg_users['participants'])){
                                                            $system_u = new Telegram\ChatTgUsers();
                                                            foreach ($tg_users['participants'] as $tg_user) {
                                                                if($tg_user['role']!='user')
                                                                    continue;
                                                                if(!$system_u->isExistsUser($tg_user['user']['id'])) {
                                                                    $fields = [
                                                                        'UF_USER_NAME' => $tg_user['user']['first_name'],
                                                                        'UF_USER_LOGIN' => $tg_user['user']['username']??'',
                                                                        'UF_CHAT_ID' => $exist_link['UF_CHAT_ID'],
                                                                        'UF_USER_ID' => $tg_user['user']['id'],
                                                                    ];
                                                                    $system_u->addUser($fields);
                                                                }
                                                            }
                                                        }
                                                    }
                                                } catch (Exception $e){
                                                    $status = $e->getMessage();
                                                }
                                            }
                                            break;
                                    }
                                }
                                $without_nick_tg_users[$link] = [];
                                $will_add_users[$link] = [];
                                if(check_full_array($tg_users)){
                                    foreach ($tg_users as $tg_user_){
                                        if(empty($tg_user_['UF_USER_LOGIN'])) {
                                            $without_nick_tg_users[$link] = $tg_user_;
                                        }
                                    }

                                    foreach ($users_with_tg[$link] as $user_ww) {
                                        foreach ($tg_users as $tg_user_) {
                                            if ($user_ww['UF_TELEGRAM'] != '@' . $tg_user_['UF_USER_LOGIN']) {
                                                $will_add_users[$link] = $user_ww;
                                            }
                                        }
                                    }
                                }
                                */?><!--
                                <tr style="height: 50px">
                                    <?php /*if($key==0){*/?>
                                        <td class="text-left items-center" rowspan="<?/*=count($role['PROPERTY_TG_CHANEL_VALUE'])*/?>"><?/*=$role['NAME']*/?></td>
                                    <?php /*}*/?>
                                    <td class="text-center"><?/*=$link*/?></td>
                                    <td class="text-center"><?/*=$status*/?></td>
                                    <?php /*if($key==0){*/?>
                                        <td class="text-center items-center" rowspan="<?/*=count($role['PROPERTY_TG_CHANEL_VALUE'])*/?>">
                                            <?/*=count($users)*/?>
                                        </td>
                                        <td class="text-center items-center" rowspan="<?/*=count($role['PROPERTY_TG_CHANEL_VALUE'])*/?>">
                                            <?/*=count($users_without_tg)*/?>
                                        </td>
                                    <?php /*}*/?>
                                    <td class="text-center"><?/*=count($tg_users)*/?></td>
                                    <td class="text-center"><?/*=count($without_nick_tg_users[$link])*/?></td>
                                    <td class="text-center"><?/*=count($will_add_users[$link])*/?></td>
                                </tr>
                            --><?php /*}*/
                        }?>
                    <?php }
                ?>
                </tbody>
            </table>

        </div>


    </div>

</div>
<style>
    .items-center{
        vertical-align: top;
    }
</style>
<?php
?>

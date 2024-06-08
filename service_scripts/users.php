<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline/session.madeline_production';
$all_users = [];
$roles = \Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']);
$groups_roles = [];
foreach ($roles as $r){
    if(count($r['PROPERTY_TG_CHANEL_VALUE'])>0) {
        foreach ($r['PROPERTY_TG_CHANEL_VALUE'] as $link__){
            $groups_roles[$link__][] = $r['ID'];
        }
    }
}
$tg_users = new Telegram\ChatTgUsers();

$header = 'Список пользователей';
if($_REQUEST['type']=='tg_users_without_logins') {
    $chat = (new \Telegram\ChatLinks())->getByChatId((int)$_REQUEST['chat_id']);
    $users = $tg_users->getArray(['UF_CHAT_ID' => (int)$_REQUEST['chat_id'], 'UF_USER_LOGIN' => false]);
    $header.=' без логинов в группе "'.$chat['UF_NAME'].'"';
} elseif($_REQUEST['type']=='will_add_to_group') {
    $will_add_users= [];
    $chat = (new \Telegram\ChatLinks())->getByChatId((int)$_REQUEST['chat_id']);
    $role = current(\Models\Role::getList(['ID' => (int)$_REQUEST['role_id']], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']));
    $users = \Models\User::getListByRole($role['ID']);
    $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat((int)$_REQUEST['chat_id']);
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
    $tg_logins_array = [];
    foreach($tg_users as $one_tg_user)
        $tg_logins_array[] = '@'.$one_tg_user['UF_USER_LOGIN'];
    foreach ($users_with_tg as $one_user) {
        if(!in_array($one_user['UF_TELEGRAM'], $tg_logins_array))
            $will_add_users[] = $one_user;
    }
    $users = $will_add_users;
    $header.=' в роли "'.$role['NAME'].'" которые будут добавлены в группу "'.$chat['UF_NAME'].'"';
} elseif($_REQUEST['type']=='will_delete_from_group') {
    $will_add_users= [];
    $role = current(\Models\Role::getList(['ID' => (int)$_REQUEST['role_id']], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']));
    $users = \Models\User::getListByRole($role['ID']);
    $chat = (new \Telegram\ChatLinks())->getByChatId((int)$_REQUEST['chat_id']);
    //dump($groups_roles[$chat['UF_LINK']]);
    $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat((int)$_REQUEST['chat_id']);
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
    $tg_logins_array = [];
    foreach($users_with_tg as $one_tg_user) {
        $tg_logins_array[$one_tg_user['UF_TELEGRAM']] = $one_tg_user;
        /*(new \Telegram\ChatTgUsers())->isAllowToDelete($chat['UF_LINK'], $one_tg_user);
        dump($one_tg_user);*/
    }
    foreach ($tg_users as $one_user) {
        $arr_key = '@'.$one_user['UF_USER_LOGIN'];
        if(!in_array($arr_key, array_keys($tg_logins_array))) {
            $will_add_users[] = $one_user;
        }
    }
    $users = $will_add_users;
    $header.=' в роли "'.$role['NAME'].'" которые будут удалены из группы "'.$chat['UF_NAME'].'"';

} else {
    $users = $tg_users->getUsersByChat((int)$_REQUEST['chat_id']);
}
foreach ($users as $user)
    $all_users[] = ['id' => $user['ID']??$user['UF_USER_ID'], 'name'=>$user['UF_USER_NAME']??($user['NAME'].' '.$user['LAST_NAME']), 'login' => $user['UF_USER_LOGIN']??$user['UF_TELEGRAM']];

?>
<div class="content-block">
    <div class="text-content text-content--long">
        <h2 class="h2 center lowercase"><?=$header?></h2>
        <a href="telegram_stat.php">Назад к таблице</a>
        <div class="table-block">
            <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                <thead class="thead-dark">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Telegram Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $key => $user){?>
                        <tr>
                            <td><?=($key+1)?></td>
                            <td><?=$user['id']??''?></td>
                            <td><?=$user['name']??''?></td>
                            <td><?=$user['login']??''?></td>
                        </tr>
                    <?php }?>
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

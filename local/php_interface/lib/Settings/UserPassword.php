<?php

namespace Settings;

use \Notifications\Methods;

class UserPassword
{
    public function __construct()
    {
    }

    public function isUserPassword($password, $user = 0)
    {
        $user = \Helpers\UserHelper::prepareUserId($user);
        $userData = \CUser::GetByID($user)->Fetch();
        return \Bitrix\Main\Security\Password::equals($userData['PASSWORD'], $password);
    }

    public function updateUserPassword($data, $user = 0)
    {
        $user = \Helpers\UserHelper::prepareUserId($user);
        return \Helpers\UserHelper::setUserValue(['PASSWORD', 'CONFIRM_PASSWORD'], $data['new_pass']);
    }

}
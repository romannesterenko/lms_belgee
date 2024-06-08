<?php

namespace Notifications;


class TelegramNotifications
{
    private $MadelineProto;
    public function __construct()
    {

        $this->MadelineProto = new \danog\MadelineProto\API('session.madeline_production');
        $this->MadelineProto->async(false);
        $this->MadelineProto->start();
    }

    public function sendMessage($recipient, $text){
        $id = \Helpers\UserHelper::getTelegramId($recipient);
        if(!empty($id))
            $this->MadelineProto->messages->sendMessage(['peer'=>$id, 'message' => $text]);
    }

}
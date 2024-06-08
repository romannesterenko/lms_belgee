<?php
namespace Telegram;
class Telegram
{
    private $madelineProto;
    public $chat;
    private $login;
    private $path;
    public function __construct()
    {
        $this->path = $_SERVER["DOCUMENT_ROOT"] . \Settings\Common::get('telegram_session_path');
        $this->madelineProto = new \danog\MadelineProto\API($this->path);
        //yield $this->madelineProto->start();
        $this->madelineProto->async(true);
    }
    public function sendMessageByUserId($user_id, $text){

        $login = str_replace('@', '', \Models\User::getTelegramLogin($user_id));
        \Helpers\Tasks::setSendMessageTask($login, $text);
        //$this->sendMessage($login, $text);

    }
    public function sendMessage($peer, $text){
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $peer, $text){
            $me = yield $proto->messages->sendMessage(['peer' => $peer, 'message' => $text]);
        });
    }
    public function getMe(){
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto){
            $me = yield $proto->getSelf();
            dump($me);
        });
    }
    public function getPwrChat($chat){
        $chat = $this->madelineProto->getFullInfo($chat);
        /*$proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $chat){
            $this->chat = yield $proto->getPwrChat($chat);
        });*/
        return $chat;
    }
    public function checkUsersFromChat($chat, $users){
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $chat, $users){
            //получаем инфу о чате
            $pwr_chat = yield $proto->getPwrChat($chat);
            //получаем инфу о пользователях
            foreach($pwr_chat['participants'] as $user)
                //если не создатель
                if($user['role'] == 'user') {
                    //если нет в списке пользователей роли, шлем его с чата
                    if(!in_array('@'.$user['user']['username'], $users)) {
                        //ставим таск на удаление
                        \Helpers\Tasks::setRemoveUserFromTGChannelTask($user['user']['username'], $chat);
                    }
                }
        });
    }
    public function checkUsersFromChatAsync($chat, $users){
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $chat, $users){
            //получаем инфу о чате
            $pwr_chat = yield $proto->getPwrChat($chat);
            //получаем инфу о пользователях
            foreach($pwr_chat['participants'] as $user)
                //если не создатель
                if($user['role'] == 'user') {
                    //если нет в списке пользователей роли, шлем его с чата
                    if(!in_array('@'.$user['user']['username'], $users)) {
                        //ставим таск на удаление
                        \Helpers\Tasks::setRemoveUserFromTGChannelTask($user['user']['username'], $chat);
                    }
                }
        });
    }
    public function createChat($title, $users){
        $params = [];
        $proto = $this->madelineProto;
        foreach ($users as $user)
            if($user['UF_TELEGRAM'])
                $params[] = $user['UF_TELEGRAM'];
        $this->madelineProto->loop(function () use ($proto, $params, $title){
            $updates = yield $proto->messages->createChat(['users'=>$params, 'title' => $title]);
        });
    }

    public function deleteChat($chat){
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $chat){
            $updates = yield $proto->messages->deleteChat(['chat_id'=>$chat]);
        });
    }

    public function inviteUserToChat($chat, $user_id)
    {
        //echo '123 ';
        //$proto = $this->madelineProto;
        $Updates = yield $this->madelineProto->channels->inviteToChannel(['channel' => $chat, 'users' => [$user_id]]);
        /*$this->madelineProto->loop(function () use ($proto, $chat, $user_id){
        });*/
    }

    public function deleteUserFromChat($chat, $user_id)
    {
        $proto = $this->madelineProto;
        $this->madelineProto->loop(function () use ($proto, $chat, $user_id){
            $updates = yield $proto->channels->editBanned(
                [
                    'channel' => $chat, 
                    'participant' => $user_id, 
                    'banned_rights' => ['_' => 'chatBannedRights', 'view_messages' => true, 'send_messages' => true, 'send_media' => true, 'send_stickers' => true, 'send_gifs' => true, 'send_games' => true, 'send_inline' => true, 'embed_links' => true, 'send_polls' => true, 'change_info' => true, 'invite_users' => true, 'pin_messages' => true, 'until_date' => 0]
                ]);
        });
    }
}


<?php

namespace Telegram;

use Helpers\HLBlockHelper as HLBlock;

class ChatTgUsers
{
    private string $dataClass;

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('tg_group_users');
    }

    public function isExistsUser($id, $chat_id)
    {
        $exists = current($this->get(['UF_USER_ID'=>$id, 'UF_CHAT_ID' => $chat_id]));
        return check_full_array($exists)&&$exists['ID']>0;
    }

    public function getUsersByChat($chat_id)
    {
        return $this->get(['UF_CHAT_ID'=>$chat_id]);
    }

    public function getArray($filter){
        return $this->get($filter);
    }

    private function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return (array)HLBlock::get($this->dataClass, $filter, $select, $order);
    }

    private function add($fields){
        HLBlock::add($fields, $this->dataClass);
    }

    public function deleteByChat($UF_CHAT_ID)
    {
        $list = $this->getUsersByChat($UF_CHAT_ID);
        dump($list);
        if(check_full_array($list)){
            try{
                foreach ($list as $item) {
                    $this->delete($item['ID']);
                }
            } catch (\Exception $e){
                dump($e->getMessage());
            }

        }
    }

    public function isAllowToDelete($UF_LINK, $one_tg_user, $role_id)
    {
        $chat = (new ChatLinks())->getByLink($UF_LINK);
        $list = $this->getUsersByChat($chat['UF_CHAT_ID']);
        foreach ($list as $item){
            if('@'.$item['UF_USER_LOGIN']!=$one_tg_user['UF_TELEGRAM']){
                dump($item);
            }
        }

        /*
        $exists = $this->getByUserAndChat($one_tg_user['UF_TELEGRAM'], $UF_LINK);
        if(!check_full_array($exists)){
            $groups_roles = [];
            foreach (\Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']) as $r){
                if(count($r['PROPERTY_TG_CHANEL_VALUE'])>0) {
                    foreach ($r['PROPERTY_TG_CHANEL_VALUE'] as $link__){
                        $groups_roles[$link__][] = $r['ID'];
                    }
                }
            }
            if(count($one_tg_user['UF_ROLE'])==1)
                return true;
            else{
                foreach ($groups_roles[$UF_LINK] as $group_id){
                    if($group_id==$role_id)
                        continue;
                    if(in_array($group_id, $one_tg_user['UF_ROLE']))
                        return false;
                }
            }
            return true;
        }*/
    }

    public function setBanned($row_id)
    {
        $this->update($row_id, ['UF_ROLE' => 'banned']);
    }

    private function update($id, $fields){
        return HLBlock::update($id, $fields, $this->dataClass);
    }

    public function addUser($fields)
    {
        $this->add($fields);
    }

    private function delete($ID)
    {
        HLBlock::delete($ID, $this->dataClass);
    }

    public function setUser($row_id)
    {
        $this->update($row_id, ['UF_ROLE' => 'user']);
    }

    public function getUserByID(mixed $UF_USER_ID)
    {
        return current($this->get(['UF_USER_ID'=>$UF_USER_ID]));
    }

    private function getByUserAndChat($login, $link)
    {
        $arr = explode('@', $login);
        $chat = (new ChatLinks())->getByLink($link);
        return current($this->get(['UF_CHAT_ID' => $chat['UF_CHAT_ID'], 'UF_USER_LOGIN' => $arr[1]]));
    }

}
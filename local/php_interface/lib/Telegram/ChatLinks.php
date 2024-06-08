<?php

namespace Telegram;

use Helpers\HLBlockHelper as HLBlock;

class ChatLinks
{
    private string $dataClass;

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('tg_chat_links');
    }

    private function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return (array)HLBlock::get($this->dataClass, $filter, $select, $order);
    }

    private function add($fields){
        HLBlock::add($fields, $this->dataClass);
    }

    public function getAll()
    {
        return $this->get(['>ID'=>0]);
    }

    public function getByChatId($chat_id)
    {
        $exists = current($this->get(['UF_CHAT_ID'=>$chat_id]));
        if(check_full_array($exists))
            return $exists;
        return [];
    }

    public function delete(mixed $ID)
    {
        return HLBlock::delete($ID, $this->dataClass);
    }

    public function deleteAll()
    {
        $list = $this->get();
        $count = count($list);
        for ($i=0; $i<=$count; $i++){
            $item = current($this->get());
            $this->delete($item['ID']);
        }
    }

    private function update($id, $fields){
        return HLBlock::update($id, $fields, $this->dataClass);
    }
    public function getByLink($link){
        $exists = current($this->get(['UF_LINK'=>$link]));
        if(check_full_array($exists))
            return $exists;
        return [];
    }
    public function addLink($chat_id, $link, $type, $name, $coment='')
    {
        $exists = $this->getByLink($link);
        if(!check_full_array($exists)) {
            $fields = [
                'UF_CHAT_ID' => $chat_id,
                'UF_LINK' => $link,
                'UF_TYPE' => $type,
                'UF_NAME' => $name,
                'UF_COMENT' => $coment,
            ];
            $this->add($fields);
        }
    }

}
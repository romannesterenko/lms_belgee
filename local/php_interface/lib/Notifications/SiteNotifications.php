<?php

namespace Notifications;

use Helpers\HLBlockHelper as HLBlock;

class SiteNotifications
{
    private string $dataClass;

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('notifications');
    }

    public function makeAsRead($id)
    {
        return $this->update($id, ['UF_IS_READ' => 'Y']);
    }

    private function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return HLBlock::get($this->dataClass, $filter, $select, $order);
    }

    private function add($fields) {
        HLBlock::add($fields, $this->dataClass);
    }

    private function update($id, $fields) {
        return HLBlock::update($id, $fields, $this->dataClass);
    }

    public function addNotification($user, $text, $priority='notify', $link='#')
    {
        $types = [
            'notify' => 1,
            'critical' => 2,
            'important' => 3
        ];
        $fields = [
            'UF_USER_ID' => $user,
            'UF_TEXT' => $text,
            'UF_TYPE' => $types[$priority]??$types['notify'],
            'UF_DATE' => date('d.m.Y H:i:s'),
            'UF_LINK' => $link,
        ];
        $this->add($fields);
    }

}
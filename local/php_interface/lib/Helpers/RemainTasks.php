<?php

namespace Helpers;
use \Helpers\HLBlockHelper;
class RemainTasks
{
    public static function init(){
        return HLBlockHelper::initialize('remain_tasks');
    }

    public static function create($fields){
        $tasks = self::init();
        $fields['UF_CREATED_AT'] = date('d.m.Y H:i:s');
        $fields['UF_COMPLETED'] = false;
        $tasks::add($fields);
    }

    public static function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        $tasks = self::init();
        return $tasks::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public static function delete($ID):void
    {
        $tasks = self::init();
        $tasks::delete($ID);
    }

    public static function setCompleted($task_id, $sended = true)
    {
        $tasks = self::init();
        $tasks::update($task_id, ['UF_COMPLETED' => 1, 'UF_SENDED' => $sended, 'UF_COMPLETED_AT' => date('d.m.Y H:i:s')]);
    }
}
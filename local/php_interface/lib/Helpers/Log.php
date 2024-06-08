<?php

namespace Helpers;


class Log
{
    public static function write($expr, $tracking = false)
    {
        $log = $_SERVER["DOCUMENT_ROOT"].'/upload/report_logs/log-'.date('d-m-Y').'.log';
        $string = 'Дата записи: '.date('d.m.Y H:i:s').PHP_EOL.PHP_EOL;
        $string.= self::prepareString($expr).PHP_EOL.PHP_EOL;
        if($tracking) {
            $string .= 'Стек вызовов' . PHP_EOL;
            $string .= self::prepareString(debug_backtrace()) . PHP_EOL . PHP_EOL;
        }

        error_log($string, 3, $log);
    }
    public static function writeCommon($expr, $directory = 'invoice'): void
    {
        $dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/logs/' . $directory;
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        $log = $dir.'/'.date('d-m-Y').'.log';
        $string = 'Дата записи: '.date('d.m.Y H:i:s').PHP_EOL.PHP_EOL;
        $string.= self::prepareString($expr).PHP_EOL.PHP_EOL;

        error_log($string, 3, $log);
    }

    public static function writeTxt($expr)
    {
        $log = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/log.txt';

        $string = self::prepareString($expr).PHP_EOL.PHP_EOL;

        error_log($string, 3, $log);
    }

    private static function prepareString($expr)
    {
        if(is_array($expr)){
            return print_r($expr, TRUE);
        }
        if(is_object($expr)){
            return print_r($expr, TRUE);
        }
        return (string)$expr;
    }

    public static function add($entity, $action, $entity_id, $user_id = 0, $before_fields = [], $after_fields=[]){
        $log_hl = \Helpers\HLBlockHelper::initialize('logs');
        $fields = [
            'UF_USER_ID' => $user_id,
            'UF_ENTITY' => $entity,
            'UF_ENTITY_ID' => $entity_id,
            'UF_ACTION' => $action,
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
            'UF_BEFORE_FIELDS' => json_encode($before_fields),
            'UF_AFTER_FIELDS' => json_encode($after_fields),
        ];
        $log_hl::add($fields);
    }

    public static function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        $logs = \Helpers\HLBlockHelper::initialize('logs');
        return $logs::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public static function limit($filter = [], $limit = 50, $select = ['*'], $order = ["ID" => "DESC"])
    {
        $logs = \Helpers\HLBlockHelper::initialize('logs');
        return $logs::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter,
            "limit" => $limit,
        ))->fetchAll();
    }

    public static function getById(mixed $ID)
    {
        $log_hl = \Helpers\HLBlockHelper::initialize('logs');
        return self::get(['ID' => $ID]);
    }

    public static function getBySubstr(string $str)
    {
        $log_hl = \Helpers\HLBlockHelper::initialize('logs');
        return self::get(['%UF_AFTER_FIELDS' => $str]);
    }

    public static function delete(mixed $ID)
    {
        $logs = \Helpers\HLBlockHelper::initialize('logs');
        return $logs::delete($ID);
    }

}
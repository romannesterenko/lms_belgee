<?php

namespace Teaching;

class Trainers
{
    public static function get($filter = [], $select = [])
    {
        $list = [];
        if(!\Helpers\IBlockHelper::includeIBlockModule())
            return [];
        $filter = $filter===[]?['ACTIVE' => 'Y']:$filter;
        $filter = array_merge(['IBLOCK_ID' => 7], $filter);
        $select = $select===[]?['ID', 'NAME']:$select;
        $res = \CIBlockElement::GetList(array('ID' => 'ASC'), $filter, false, false, $select);
        while ($ob = $res->GetNextElement()) {
            $list[] = \Helpers\PropertyHelper::collectFields($ob->GetFields());
        }
        return $list;
    }

    public static function isExistsByUser($user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = self::get(['PROPERTY_USER'=>$user_id]);
        $list = $list===[]?[]:current($list);
        return $list['ID']>0;
    }

    public static function getArrayBySchedule(mixed $ID)
    {
        $shedule = SheduleCourses::getArray(['ID' => $ID]);
        dump($shedule);
    }

    public static function getAll()
    {
        return self::get(['ACTIVE' => 'Y']);
    }
}
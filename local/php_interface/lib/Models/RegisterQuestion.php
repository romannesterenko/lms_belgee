<?php

namespace Models;

class RegisterQuestion
{

    public static function find($question_id, $select = [])
    {
        $list = current(self::getList(['ID' => $question_id], $select));
        return check_full_array($list)?$list:[];
    }

    public static function getList($filter, $select=[])
    {
        \CModule::IncludeModule('iblock');
        $list = [];
        $arFilter = array_merge(["IBLOCK_ID" => 18], $filter);
        $res = \CIBlockElement::GetList(array('NAME'=>'ASC'), $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function findByCourse($course_id)
    {
        return self::getList(['PROPERTY_COURSE' => $course_id], ['ID', 'PROPERTY_COURSE', 'PROPERTY_TITLE', 'PROPERTY_TYPE']);
    }
}
<?php

namespace Models;
use Helpers\DateHelper;
use Helpers\HLBlockHelper as HLBlock;
use Settings\Common;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\SheduleCourses;

class Sertificate
{
    private string $dataClass;
    public function __construct() {
        $this->dataClass = HLBlock::initialize('sertificates');
    }

    public static function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        return HLBlock::get(
            HLBlock::initialize('sertificates'),
            $filter,
            $select,
            $order
        );
    }

    public static function update($id, $fields)
    {
        $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
        HLBlock::update($id, $fields, HLBlock::initialize('sertificates'));
    }

    public static function delete($id) {
        HLBlock::delete($id, HLBlock::initialize('sertificates'));
    }

    public static function create($fields)
    {
        HLBlock::add($fields, HLBlock::initialize('sertificates'));
    }

    public static function getByCompletion(mixed $UF_COMPLETION_ID)
    {
        $exists = self::get(['UF_COMPLETION_ID' => $UF_COMPLETION_ID]);
        return check_full_array($exists)?current($exists):null;
    }
}
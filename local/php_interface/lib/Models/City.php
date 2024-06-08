<?php

namespace Models;
use Helpers\HLBlockHelper as HLBlock;

class City
{
    private string  $dataClass;
    public function __construct() {
        $this->dataClass = HLBlock::initialize('city');
    }

    public static function get($filter, $select = ['*'], $order = ['ID' => "DESC"])
    {
        return HLBlock::get(
            HLBlock::initialize('city'),
            $filter,
            $select,
            $order
        );
    }

    public static function delete(mixed $ID):void
    {
        HLBlock::delete($ID, HLBlock::initialize('city'));
    }

    public static function update($ID, $fields)
    {
        HLBlock::update($ID, $fields, HLBlock::initialize('city'));
    }

    public static function create($fields)
    {
        HLBlock::add($fields, HLBlock::initialize('city'));
    }

}
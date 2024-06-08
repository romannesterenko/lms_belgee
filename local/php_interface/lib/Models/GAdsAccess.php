<?php

namespace Models;
use Helpers\HLBlockHelper as HLBlock;

class GAdsAccess
{
    private string $dataClass;
    public function __construct() {
        $this->dataClass = HLBlock::initialize('g_ads_access');
    }

    public static function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        return HLBlock::get(
            HLBlock::initialize('g_ads_access'),
            $filter,
            $select,
            $order
        );
    }

    public static function update($id, $fields)
    {
        $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
        HLBlock::update($id, $fields, HLBlock::initialize('g_ads_access'));
    }

    public static function delete($id) {
        HLBlock::delete($id, HLBlock::initialize('g_ads_access'));
    }

    public static function create($fields)
    {
        return HLBlock::add($fields, HLBlock::initialize('g_ads_access'));
    }
}